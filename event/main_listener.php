<?php
/**
 *
 * Reduce Search Index [RSI]. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2020-2021, Dark❶, https://dark1.tech
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace dark1\reducesearchindex\event;

/**
 * @ignore
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use dark1\reducesearchindex\core\consts;
use phpbb\config\config;
use phpbb\db\driver\driver_interface as db_driver;
use phpbb\cache\driver\driver_interface as cache_driver;
use phpbb\template\template;
use phpbb\user;
use phpbb\language\language;

/**
 * Reduce Search Index Event listener.
 */
class main_listener implements EventSubscriberInterface
{
	/** @var config */
	protected $config;

	/** @var db_driver */
	protected $db;

	/** @var cache_driver */
	protected $cache;

	/** @var template */
	protected $template;

	/** @var user */
	protected $user;

	/** @var language */
	protected $language;

	/**
	 * Constructor for listener
	 *
	 * @param config		$config		phpBB config
	 * @param db_driver		$db			phpBB DBAL object
	 * @param cache_driver	$cache		phpBB Cache object
	 * @param template		$template	phpBB template
	 * @param user			$user		phpBB user
	 * @param language		$language	phpBB language object
	 * @access public
	 */
	public function __construct(config $config, db_driver $db, cache_driver $cache, template $template, user $user, language $language)
	{
		$this->config		= $config;
		$this->db			= $db;
		$this->cache		= $cache;
		$this->template		= $template;
		$this->user			= $user;
		$this->language		= $language;
	}

	/**
	* Assign functions defined in this class to event listeners in the core
	*
	* @return array
	* @static
	* @access public
	*/
	public static function getSubscribedEvents()
	{
		return [
			'core.search_modify_submit_parameters'		=> 'search_modify_submit_parameters',
			'core.search_native_index_before'			=> 'search_native_index_before',
		];
	}



	/**
	 * Search Page Notice
	 *
	 * @return null
	 * @access public
	 */
	public function search_modify_submit_parameters()
	{
		if ($this->config['dark1_rsi_enable'])
		{
			$this->language->add_lang('lang_rsi', 'dark1/reducesearchindex');
			$this->template->assign_vars([
				'RSI_SEARCH_FLAG'		=> $this->config['dark1_rsi_enable'],
				'RSI_SEARCH_TIME'		=> $this->user->create_datetime()->setTimestamp((int) $this->config['dark1_rsi_time']),
			]);
		}
	}



	/**
	 * Search Native Index Before
	 *
	 * @param object $event	Event object
	 * @return null
	 * @access public
	 */
	public function search_native_index_before($event)
	{
		$post_id = $event['post_id'];
		$words = $event['words'];

		if ($this->config['dark1_rsi_enable'])
		{
			$forum = $this->get_search_forum($post_id);

			if ($forum['dark1_rsi_f_enable'] >= consts::F_ENABLE_TOPIC && $forum['topic_time'] <= $this->config['dark1_rsi_time'])
			{
				$words['add']['post'] = $words['add']['title'] = $words['del']['post'] = $words['del']['title'] = [];
			}
			else if ($forum['dark1_rsi_f_enable'] == consts::F_ENABLE_POST && $forum['post_time'] <= $this->config['dark1_rsi_time'])
			{
				$words['add']['post'] = $words['add']['title'] = $words['del']['post'] = $words['del']['title'] = [];
			}
		}

		$event['words'] = $words;
	}



	/**
	 * Get Search Forum
	 *
	 * @param int $post_id	Post ID
	 * @return array Forum
	 * @access private
	 */
	private function get_search_forum($post_id)
	{
		// Get search matrix data from the cache
		$search_matrix = $this->cache->get(consts::CACHE_KEY);

		if ($search_matrix === false || !isset($search_matrix[$post_id]))
		{
			$sql_ary = [
				'SELECT'	=> 'f.dark1_rsi_f_enable, t.topic_time, p.post_time',
				'FROM'		=> [
					POSTS_TABLE		=> 'p',
				],
				'LEFT_JOIN'	=> [[
					'FROM'	=> [TOPICS_TABLE => 't'],
					'ON'	=> 't.topic_id = p.topic_id',
				], [
					'FROM'	=> [FORUMS_TABLE => 'f'],
					'ON'	=> 'f.forum_id = p.forum_id',
				],],
				'WHERE'	=> 'p.post_id = ' . (int) $post_id,
			];
			$sql = $this->db->sql_build_query('SELECT', $sql_ary);
			$result = $this->db->sql_query($sql);
			$rows = $this->db->sql_fetchrowset($result);
			$this->db->sql_freeresult($result);
			foreach ($rows as $row)
			{
				$search_matrix[$post_id] = $row;
			}

			// Cache post matrix data
			$this->cache->put(consts::CACHE_KEY, $search_matrix);
		}

		return $search_matrix[$post_id];
	}
}
