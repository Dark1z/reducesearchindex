<?php
/**
 *
 * Reduce Search Index [RSI]. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2020-forever, Dark❶, https://dark1.tech
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
use phpbb\db\driver\driver_interface as db_driver;
use phpbb\log\log;
use phpbb\auth\auth;
use phpbb\user;
use phpbb\event\dispatcher_interface as dispatcher;
use phpbb\search\fulltext_native;

/**
 * Reduce Search Index Cron Task.
 */
class auto_reduce_sync extends base
{

	/** @var config */
	protected $config;

	/** @var db_driver */
	protected $db;

	/** @var log */
	protected $phpbb_log;

	/** @var auth */
	protected $auth;

	/** @var user */
	protected $user;

	/** @var dispatcher */
	protected $phpbb_dispatcher;

	/** @var string phpBB root path */
	protected $phpbb_root_path;

	/** @var string phpBB php ext */
	protected $php_ext;

	/**
	* Constructor for cron task
	*
	* @param config			$config				phpBB config
	* @param db_driver		$db					phpBB DBAL object
	* @param log			$phpbb_log			phpBB log
	* @param auth			$auth				phpBB auth
	* @param user			$user				phpBB user
	* @param dispatcher		$dispatcher			phpBB dispatcher
	* @param string			$phpbb_root_path	phpBB root path
	* @param string			$php_ext			phpBB php ext
	* @access public
	*/
	public function __construct(config $config, db_driver $db, log $phpbb_log, auth $auth, user $user, dispatcher $phpbb_dispatcher, $phpbb_root_path, $php_ext)
	{
		$this->config			= $config;
		$this->db				= $db;
		$this->phpbb_log		= $phpbb_log;
		$this->auth				= $auth;
		$this->user				= $user;
		$this->phpbb_dispatcher	= $phpbb_dispatcher;
		$this->phpbb_root_path	= $phpbb_root_path;
		$this->php_ext			= $php_ext;
	}

	/**
	* Returns whether this cron task can run, given current board configuration.
	*
	* @return bool
	*/
	public function is_runnable()
	{
		return (bool) $this->config['dark1_rsi_auto_reduce_sync_enable'];
	}

	/**
	* Returns whether this cron task should run now, because enough time has passed since it was last run.
	*
	* @return bool
	*/
	public function should_run()
	{
		return (bool) ($this->config['dark1_rsi_auto_reduce_sync_last_gc'] < (time() - $this->config['dark1_rsi_auto_reduce_sync_gc']));
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
			$topic_ids = $this->array_unique_sort($topic_ary['topic_ids']);

			// Lock Topics
			$this->lock_topics($topic_ids);

			// Remove the message from the search index
			$this->reduce_search_index($post_ids, $poster_ids, $forum_ids);

			$dark1_rsi_interval = $this->config['dark1_rsi_interval'] / 86400;
			$dark1_rsi_time = date(consts::TIME_FORMAT, (int) $this->config['dark1_rsi_time']);
			$this->phpbb_log->add('admin', ANONYMOUS, '127.0.0.1', 'RSI_AUTO_LOG', time(), [$dark1_rsi_interval, $dark1_rsi_time]);
		}

		// Update the last run time
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
		$post_ids = $poster_ids = $forum_ids = $topic_ids = [];

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
		$rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		foreach ($rows as $row)
		{
			$post_ids[] = (int) $row['post_id'];
			$poster_ids[] = (int) $row['poster_id'];
			$forum_ids[] = (int) $row['forum_id'];

			if ($row['dark1_rsi_f_enable'] == consts::F_ENABLE_LOCK)
			{
				$topic_ids[] = (int) $row['topic_id'];
			}
		}

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
		$post_ids = $poster_ids = $forum_ids = [];

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
		$rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		foreach ($rows as $row)
		{
			$post_ids[] = (int) $row['post_id'];
			$poster_ids[] = (int) $row['poster_id'];
			$forum_ids[] = (int) $row['forum_id'];
		}

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
			$topic_ids = array_chunk($topic_ids, 50, true);
			foreach ($topic_ids as $topic_ids_chunk)
			{
				$sql = 'UPDATE ' . TOPICS_TABLE .
						' SET topic_status = ' . ITEM_LOCKED  .
						' WHERE ' . $this->db->sql_in_set('topic_id', $topic_ids_chunk);
				$this->db->sql_query($sql);
			}
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
		$search = $this->config['search_type'];
		$name = substr($search, strrpos($search, '\\') + 1);

		if ($name == 'fulltext_native' && class_exists($search) && count($post_ids) > 0)
		{
			/** @var fulltext_native */
			$search = new $search(false, $this->phpbb_root_path, $this->php_ext, $this->auth, $this->config, $this->db, $this->user, $this->phpbb_dispatcher);

			$post_ids = array_chunk($post_ids, 50, true);
			foreach ($post_ids as $post_ids_chunk)
			{
				$this->db->sql_transaction('begin');
				$search->index_remove($post_ids_chunk, $poster_ids, $forum_ids);
				$this->db->sql_transaction('commit');
			}
		}
	}
}
