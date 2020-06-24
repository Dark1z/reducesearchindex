<?php
/**
 *
 * Reduce Search Index [RSI]. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2020, Dark❶, https://dark1.tech
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace dark1\reducesearchindex\cron;

/**
 * @ignore
 */
use phpbb\cron\task\base;
use dark1\reducesearchindex\core\consts;
use phpbb\config\config;
use phpbb\db\driver\driver_interface;
use phpbb\log\log;
use phpbb\auth\auth;
use phpbb\user;
use phpbb\event\dispatcher;

/**
 * Reduce Search Index Cron Task.
 */
class auto_reduce_sync extends base
{

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\log\log */
	protected $phpbb_log;

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\event\dispatcher */
	protected $phpbb_dispatcher;

	/** @var string phpBB root path */
	protected $phpbb_root_path;

	/** @var string phpBB phpEx */
	protected $phpEx;

	/**
	* Constructor for cron task
	*
	* @param \phpbb\config\config				$config				phpBB config
	* @param \phpbb\db\driver\driver_interface	$db					phpBB DBAL object
	* @param \phpbb\log\log						$phpbb_log			phpBB log
	* @param \phpbb\auth\auth					$auth				phpBB auth
	* @param \phpbb\user						$user				phpBB user
	* @param \phpbb\event\dispatcher			$dispatcher			phpBB dispatcher
	* @param string								$phpbb_root_path	phpBB root path
	* @param string								$phpEx				phpBB phpEx
	* @access public
	*/
	public function __construct(config $config, driver_interface $db, log $phpbb_log, auth $auth, user $user, dispatcher $phpbb_dispatcher, $phpbb_root_path, $phpEx)
	{
		$this->config			= $config;
		$this->db				= $db;
		$this->phpbb_log		= $phpbb_log;
		$this->auth				= $auth;
		$this->user				= $user;
		$this->phpbb_dispatcher	= $phpbb_dispatcher;
		$this->phpbb_root_path	= $phpbb_root_path;
		$this->phpEx			= $phpEx;
	}

	/**
	* Returns whether this cron task can run, given current board configuration.
	*
	* @return bool
	*/
	public function is_runnable()
	{
		return ($this->config['dark1_rsi_auto_reduce_sync_enable'] && $this->config['dark1_rsi_enable']);
	}

	/**
	* Returns whether this cron task should run now, because enough time has passed since it was last run.
	*
	* @return bool
	*/
	public function should_run()
	{
		return (($this->config['dark1_rsi_auto_reduce_sync_last_gc'] < (time() - $this->config['dark1_rsi_auto_reduce_sync_gc'])) && ($this->config['dark1_rsi_time'] < (time() - $this->config['dark1_rsi_interval'])));
	}

	/**
	* Runs this cron task.
	*
	* @return null
	*/
	public function run()
	{
		if ($this->config['dark1_rsi_enable'] && ($this->config['dark1_rsi_time'] < (time() - $this->config['dark1_rsi_interval'])))
		{
			$this->config->set('dark1_rsi_time', (time() - $this->config['dark1_rsi_interval']), false);

			// Get Data
			$topic_ary = $this->get_topic_ary();
			$post_ary = $this->get_post_ary();

			// Set Data
			$post_ids = $this->array_unique_sort(array_merge($topic_ary['post_ids'], $post_ary['post_ids']));
			$poster_ids = $this->array_unique_sort(array_merge($topic_ary['poster_ids'], $post_ary['poster_ids']));
			$forum_ids = $this->array_unique_sort(array_merge($topic_ary['forum_ids'], $post_ary['forum_ids']));

			// Lock Topics
			$this->lock_topics($topic_ary['topic_ids']);

			// Remove the message from the search index
			$this->reduce_search_index($post_ids, $poster_ids, $forum_ids);

			$dark1_rsi_interval = $this->config['dark1_rsi_interval'] / 86400;
			$dark1_rsi_time = date('Y-m-d h:i:s A P', (int) $this->config['dark1_rsi_time']);
			$this->phpbb_log->add('admin', 'ANONYMOUS', '127.0.0.1', 'RSI_AUTO_LOG', time(), array($dark1_rsi_interval, $dark1_rsi_time));
		}

		// Update the last backup time
		$this->config->set('dark1_rsi_auto_reduce_sync_last_gc', time(), false);
	}

	/**
	* Array to Uniquely Sort the IDs.
	*
	* @param array		$ary_ids		Array with IDs
	* @return array
	* @access private
	*/
	private function array_unique_sort($ary_ids)
	{
		$ary_ids = array_unique($ary_ids);
		sort($ary_ids, SORT_NUMERIC);
		return $ary_ids;
	}

	/**
	* Get Topic Array.
	*
	* @return array
	* @access private
	*/
	private function get_topic_ary()
	{
		$post_ids = $poster_ids = $forum_ids = $topic_ids = array();

		$sql_ary = [
			'SELECT'	=> 't.topic_id, p.post_id, p.poster_id, p.forum_id, f.dark1_rsi_f_enable',
			'FROM'		=> [
				POSTS_TABLE		=> 'p',
			],
			'LEFT_JOIN' => [
				[
					'FROM'	=> [TOPICS_TABLE => 't'],
					'ON'	=> 't.topic_id = p.topic_id',
				],
				[
					'FROM'	=> [FORUMS_TABLE => 'f'],
					'ON'	=> 'f.forum_id = p.forum_id',
				],
			],
			'WHERE'	=> 'f.dark1_rsi_f_enable >= ' . (int) consts::F_ENABLE_TOPIC . ' AND t.topic_time <= ' . (int) $this->config['dark1_rsi_time'],
		];
		$sql = $this->db->sql_build_query('SELECT', $sql_ary);
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$post_ids[] = (int) $row['post_id'];
			$poster_ids[] = (int) $row['poster_id'];
			$forum_ids[] = (int) $row['forum_id'];

			if ($row['dark1_rsi_f_enable'] == consts::F_ENABLE_LOCK)
			{
				$topic_ids[] = (int) $row['topic_id'];
			}
		}
		$this->db->sql_freeresult($result);

		return [
			'post_ids' => $post_ids,
			'poster_ids' => $poster_ids,
			'forum_ids' => $forum_ids,
			'topic_ids' => $topic_ids,
		];
	}

	/**
	* Get Post Array.
	*
	* @return array
	* @access private
	*/
	private function get_post_ary()
	{
		$post_ids = $poster_ids = $forum_ids = array();

		$sql_ary = [
			'SELECT'	=> 'p.post_id, p.poster_id, p.forum_id',
			'FROM'		=> [
				POSTS_TABLE		=> 'p',
			],
			'LEFT_JOIN' => [
				[
					'FROM'	=> [FORUMS_TABLE => 'f'],
					'ON'	=> 'f.forum_id = p.forum_id',
				],
			],
			'WHERE'	=> 'f.dark1_rsi_f_enable = ' . (int) consts::F_ENABLE_POST . ' AND p.post_time <= ' . (int) $this->config['dark1_rsi_time'],
		];
		$sql = $this->db->sql_build_query('SELECT', $sql_ary);
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$post_ids[] = (int) $row['post_id'];
			$poster_ids[] = (int) $row['poster_id'];
			$forum_ids[] = (int) $row['forum_id'];
		}
		$this->db->sql_freeresult($result);

		return [
			'post_ids' => $post_ids,
			'poster_ids' => $poster_ids,
			'forum_ids' => $forum_ids,
		];
	}

	/**
	* Lock Topics using Topic IDs.
	*
	* @param array		$topic_ids		Array with Topic IDs
	* @return void
	* @access private
	*/
	private function lock_topics($topic_ids)
	{
		if (count($topic_ids) > 0)
		{
			$sql = 'UPDATE ' . TOPICS_TABLE .
					'SET topic_status = ' . ITEM_LOCKED  .
					'WHERE ' . $this->db->sql_in_set('topic_id', $topic_ids);
			$this->db->sql_query($sql);
		}
	}

	/**
	* Reduce Search Index using Post & Poster & Forum IDs.
	*
	* @param array		$post_ids		Array with Post IDs
	* @param array		$poster_ids		Array with Poster IDs
	* @param array		$forum_ids		Array with Forum IDs
	* @return void
	* @access private
	*/
	private function reduce_search_index($post_ids, $poster_ids, $forum_ids)
	{
		$search_type = $this->config['search_type'];
		$identifier = substr($search_type, strrpos($search_type, '\\') + 1);
		if ($identifier == 'fulltext_native' && class_exists($search_type))
		{
			$error = false;
			$search = new $search_type($error, $this->phpbb_root_path, $this->phpEx, $this->auth, $this->config, $this->db, $this->user, $this->phpbb_dispatcher);
			if ($error === false)
			{
				@$search->index_remove($post_ids, $poster_ids, $forum_ids);
			}
		}
	}

}
