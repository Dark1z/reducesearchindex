<?php
/**
 *
 * Reduce Search Index [RSI]. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2020, Dark❶, https://dark1.tech
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace dark1\reducesearchindex\event;

/**
 * @ignore
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use phpbb\config\config;
use phpbb\db\driver\driver_interface;
use phpbb\template\template;
use phpbb\user;

/**
 * Reduce Search Index Event listener.
 */
class main_listener implements EventSubscriberInterface
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/**
	 * Constructor for listener
	 *
	 * @param \phpbb\config\config				$config		phpBB config
	 * @param \phpbb\db\driver\driver_interface	$db			phpBB DBAL object
	 * @param \phpbb\template\template			$template	phpBB template
	 * @param \phpbb\user						$user		phpBB user
	 * @access public
	 */
	public function __construct(config $config, driver_interface $db, template $template, user $user)
	{
		$this->config				= $config;
		$this->db					= $db;
		$this->template				= $template;
		$this->user					= $user;
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
		return array(
			'core.user_setup'							=> 'load_language_on_setup',
			'core.page_header_after'					=> 'page_header_after',
			'core.search_native_index_before'			=> 'search_native_index_before',
		);
	}



	/**
	 * Load common language files during user setup
	 *
	 * @param \phpbb\event\data $event	Event object
	 * @return null
	 * @access public
	 */
	public function load_language_on_setup($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = array(
			'ext_name' => 'dark1/reducesearchindex',
			'lang_set' => 'lang_rsi',
		);
		$event['lang_set_ext'] = $lang_set_ext;
	}



	/**
	 * Search Page Header After
	 *
	 * @param \phpbb\event\data $event	Event object
	 * @return null
	 * @access public
	 */
	public function page_header_after($event)
	{
		if ($this->config['dark1_rsi_enable'])
		{
			$this->template->assign_vars(array(
				'RSI_SEARCH_FLAG'		=> $this->config['dark1_rsi_enable'],
				'RSI_SEARCH_TIME'		=> $this->user->create_datetime()->setTimestamp((int) $this->config['dark1_rsi_time']),
			));
		}
	}



	/**
	 * Search Native Index Before
	 *
	 * @param \phpbb\event\data $event	Event object
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

			if ($forum['dark1_rsi_f_enable'] >= 2 && $forum['topic_time'] <= $this->config['dark1_rsi_time'])
			{
				$words['add']['post'] = $words['add']['title'] = $words['del']['post'] = $words['del']['title'] = array();
			}
			else if ($forum['dark1_rsi_f_enable'] == 1 && $forum['post_time'] <= $this->config['dark1_rsi_time'])
			{
				$words['add']['post'] = $words['add']['title'] = $words['del']['post'] = $words['del']['title'] = array();
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
		$sql = 'SELECT f.dark1_rsi_f_enable, t.topic_time, p.post_time' . PHP_EOL .
				'FROM ' . POSTS_TABLE . ' as p' . PHP_EOL .
				'LEFT JOIN ' . TOPICS_TABLE . ' as t' . PHP_EOL .
				'ON t.topic_id = p.topic_id' . PHP_EOL .
				'LEFT JOIN ' . FORUMS_TABLE . ' as f' . PHP_EOL .
				'ON f.forum_id = p.forum_id' . PHP_EOL .
				'WHERE p.post_id = ' . (int) $post_id;
		$result = $this->db->sql_query($sql);
		$forum = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $forum;
	}
}
