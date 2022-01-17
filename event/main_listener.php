<?php
/**
 *
 * Reduce Search Index [RSI]. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2020-forever, Dark❶, https://dark1.tech
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
use phpbb\config\db_text as config_text;

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

	/** @var config_text */
	protected $config_text;

	/**
	 * Constructor for listener
	 *
	 * @param config		$config			phpBB config
	 * @param db_driver		$db				phpBB DBAL object
	 * @param cache_driver	$cache			phpBB Cache object
	 * @param template		$template		phpBB template
	 * @param user			$user			phpBB user
	 * @param language		$language		phpBB language object
	 * @param config_text	$config_text	phpBB config text
	 * @access public
	 */
	public function __construct(config $config, db_driver $db, cache_driver $cache, template $template, user $user, language $language, config_text $config_text)
	{
		$this->config		= $config;
		$this->db			= $db;
		$this->cache		= $cache;
		$this->template		= $template;
		$this->user			= $user;
		$this->language		= $language;
		$this->config_text	= $config_text;
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
			$common_words_ary = $this->get_common_words_ary();
			$this->template->assign_vars([
				'RSI_SEARCH_FLAG'			=> $this->config['dark1_rsi_enable'],
				'RSI_SEARCH_TIME'			=> $this->user->create_datetime()->setTimestamp((int) $this->config['dark1_rsi_time']),
				'RSI_SEARCH_IGN_COM_FLAG'	=> $this->config['dark1_rsi_ign_com_enable'],
				'RSI_SEARCH_IGN_COM_WORDS'	=> implode(', ', $common_words_ary),
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
		$words = $event['words'];
		$cur_words = $event['cur_words'];

		if ($this->config['dark1_rsi_enable'])
		{
			if ($this->get_search_forum_enable((int) $event['post_id']))
			{
				$words['add']['post'] = $words['add']['title'] = $words['del']['post'] = $words['del']['title'] = [];
			}
			else if ($this->config['dark1_rsi_ign_com_enable'])
			{
				$common_words_ary = $this->get_common_words_ary();
				$words['add']['post'] = array_diff($words['add']['post'], $common_words_ary);
				$words['add']['title'] = array_diff($words['add']['title'], $common_words_ary);
				$words['del']['post'] = array_unique(array_merge($words['del']['post'], array_intersect(array_keys($cur_words['post']), $common_words_ary)));
				$words['del']['title'] = array_unique(array_merge($words['del']['title'], array_intersect(array_keys($cur_words['title']), $common_words_ary)));
			}
		}

		$event['words'] = $words;
	}



	/**
	 * Get Common Words array
	 *
	 * @return array Common Words
	 * @access private
	 */
	private function get_common_words_ary()
	{
		return explode("\n", (string) $this->config_text->get('dark1_rsi_ign_com_words'));
	}



	/**
	 * Get Search Forum Enable or Not
	 *
	 * @param int $post_id	Post ID
	 * @return bool Enabled or Not
	 * @access private
	 */
	private function get_search_forum_enable($post_id)
	{
		$forum = $this->get_search_forum($post_id);
		return (bool) (
			($forum['dark1_rsi_f_enable'] >= consts::F_ENABLE_TOPIC && $forum['topic_time'] <= $this->config['dark1_rsi_time'])
			|| ($forum['dark1_rsi_f_enable'] == consts::F_ENABLE_POST && $forum['post_time'] <= $this->config['dark1_rsi_time'])
		);
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
