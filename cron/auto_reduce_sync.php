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
use phpbb\config\config;
use phpbb\db\driver\driver_interface;
use phpbb\log\log;
use Symfony\Component\DependencyInjection\ContainerInterface;

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

	/** @var ContainerInterface */
	protected $phpbb_container;

	/**
	* Constructor for cron task
	*
	* @param \phpbb\config\config				$config			phpBB config
	* @param \phpbb\db\driver\driver_interface	$db				phpBB DBAL object
	* @param \phpbb\log\log						$phpbb_log		phpBB log
	* @param ContainerInterface					$phpbb_container
	* @access public
	*/
	public function __construct(config $config, driver_interface $db, log $phpbb_log, ContainerInterface $phpbb_container)
	{
		$this->config					= $config;
		$this->db						= $db;
		$this->phpbb_log				= $phpbb_log;
		$this->phpbb_container			= $phpbb_container;
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
			$post_ids = $poster_ids = $topic_ids = $forum_ids = array();

			$sql = 'SELECT t.topic_id, p.post_id, p.poster_id, p.forum_id' . PHP_EOL .
					'FROM ' . POSTS_TABLE . ' as p' . PHP_EOL .
					'LEFT JOIN ' . TOPICS_TABLE . ' as t' . PHP_EOL .
					'ON t.topic_id = p.topic_id' . PHP_EOL .
					'LEFT JOIN ' . FORUMS_TABLE . ' as f' . PHP_EOL .
					'ON f.forum_id = p.forum_id' . PHP_EOL .
					'WHERE f.dark1_rsi_f_enable = 3 AND t.topic_time <= ' . (int) $this->config['dark1_rsi_time'];
			$result = $this->db->sql_query($sql);

			while ($row = $this->db->sql_fetchrow($result))
			{
				$post_ids[] = (int) $row['post_id'];
				$poster_ids[] = (int) $row['poster_id'];
				$topic_ids[] = (int) $row['topic_id'];
				$forum_ids[] = (int) $row['forum_id'];
			}
			$this->db->sql_freeresult($result);

			$sql = 'SELECT p.post_id, p.poster_id, p.forum_id' . PHP_EOL .
					'FROM ' . POSTS_TABLE . ' as p' . PHP_EOL .
					'LEFT JOIN ' . TOPICS_TABLE . ' as t' . PHP_EOL .
					'ON t.topic_id = p.topic_id' . PHP_EOL .
					'LEFT JOIN ' . FORUMS_TABLE . ' as f' . PHP_EOL .
					'ON f.forum_id = p.forum_id' . PHP_EOL .
					'WHERE f.dark1_rsi_f_enable = 2 AND t.topic_time <= ' . (int) $this->config['dark1_rsi_time'];
			$result = $this->db->sql_query($sql);

			while ($row = $this->db->sql_fetchrow($result))
			{
				$post_ids[] = (int) $row['post_id'];
				$poster_ids[] = (int) $row['poster_id'];
				$forum_ids[] = (int) $row['forum_id'];
			}
			$this->db->sql_freeresult($result);

			$sql = 'SELECT p.post_id, p.poster_id, p.forum_id' . PHP_EOL .
					'FROM ' . POSTS_TABLE . ' as p' . PHP_EOL .
					'LEFT JOIN ' . FORUMS_TABLE . ' as f' . PHP_EOL .
					'ON f.forum_id = p.forum_id' . PHP_EOL .
					'WHERE f.dark1_rsi_f_enable = 1 AND p.post_time <= ' . (int) $this->config['dark1_rsi_time'];
			$result = $this->db->sql_query($sql);

			while ($row = $this->db->sql_fetchrow($result))
			{
				$post_ids[] = (int) $row['post_id'];
				$poster_ids[] = (int) $row['poster_id'];
				$forum_ids[] = (int) $row['forum_id'];
			}
			$this->db->sql_freeresult($result);

			$post_ids = $this->array_unique_sort($post_ids);
			$poster_ids = $this->array_unique_sort($poster_ids);
			$topic_ids = $this->array_unique_sort($topic_ids);
			$forum_ids = $this->array_unique_sort($forum_ids);

			// Lock Topics
			if (count($topic_ids) > 0)
			{
				$sql = 'UPDATE ' . TOPICS_TABLE . PHP_EOL .
						'SET topic_status = ' . ITEM_LOCKED  . PHP_EOL .
						'WHERE ' . $this->db->sql_in_set('topic_id', $topic_ids);
				$this->db->sql_query($sql);
			}

			// Remove the message from the search index
			$search_type = $this->config['search_type'];
			$identifier = substr($search_type, strrpos($search_type, '\\') + 1);
			if ($identifier == 'fulltext_native' && class_exists($search_type))
			{
				$error = false;
				$phpbb_root_path = $this->phpbb_container->getParameter('core.root_path');
				$phpEx = $this->phpbb_container->getParameter('core.php_ext');
				$auth = $this->phpbb_container->get('auth');
				$user = $this->phpbb_container->get('user');
				$phpbb_dispatcher = $this->phpbb_container->get('dispatcher');

				$search = new $search_type($error, $phpbb_root_path, $phpEx, $auth, $this->config, $this->db, $user, $phpbb_dispatcher);
				if ($error == false)
				{
					@$search->index_remove($post_ids, $poster_ids, $forum_ids);
				}
			}

			$dark1_rsi_interval = $this->config['dark1_rsi_interval'] / 86400;
			$dark1_rsi_time = date('Y-m-d h:i:s A P', $this->config['dark1_rsi_time']);
			$this->phpbb_log->add('admin', '', '', 'RSI_AUTO_LOG', time(), array($dark1_rsi_interval, $dark1_rsi_time));
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

}
