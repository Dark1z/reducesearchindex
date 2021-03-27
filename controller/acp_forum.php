<?php
/**
 *
 * Reduce Search Index [RSI]. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2020-2021, Dark❶, https://dark1.tech
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace dark1\reducesearchindex\controller;

/**
 * @ignore
 */
use dark1\reducesearchindex\core\consts;
use phpbb\language\language;
use phpbb\log\log;
use phpbb\request\request;
use phpbb\template\template;
use phpbb\user;
use phpbb\db\driver\driver_interface as db_driver;
use phpbb\cache\driver\driver_interface as cache_driver;
use dark1\reducesearchindex\core\forum_map_rsi;

/**
 * Reduce Search Index [RSI] ACP controller Forum.
 */
class acp_forum extends acp_base
{
	/** @var db_driver */
	protected $db;

	/** @var cache_driver */
	protected $cache;

	/** @var forum_map_rsi */
	protected $forum_map_rsi;

	/**
	 * Constructor.
	 *
	 * @param language			$language		Language object
	 * @param log				$log			Log object
	 * @param request			$request		Request object
	 * @param template			$template		Template object
	 * @param user				$user			User object
	 * @param db_driver			$db				Database object
	 * @param cache_driver		$cache			Cache object
	 * @param forum_map_rsi		$forum_map_rsi	Forum Map RSI
	 */
	public function __construct(language $language, log $log, request $request, template $template, user $user, db_driver $db, cache_driver $cache, forum_map_rsi $forum_map_rsi)
	{
		parent::__construct($language, $log, $request, $template, $user);

		$this->db				= $db;
		$this->cache			= $cache;
		$this->forum_map_rsi	= $forum_map_rsi;
	}

	/**
	 * Display the options a user can configure for Forum Mode.
	 *
	 * @return void
	 * @access public
	 */
	public function handle()
	{
		// Is the form being submitted to us?
		if ($this->request->is_set_post('submit'))
		{
			$this->check_form_on_submit();
			$this->submit_forums();
			$this->success_form_on_submit();
		}

		// Set output variables for display in the template
		$this->print_forums();
	}

	/**
	 * Display the Forum options.
	 *
	 * @return void
	 * @access private
	 */
	private function print_forums()
	{
		$forum_tpl_rows = $this->forum_map_rsi->main();

		foreach ($forum_tpl_rows as $tpl_row)
		{
			$this->template->assign_block_vars('forumrow', $tpl_row);
		}
	}

	/**
	 * Submit the Forum options.
	 *
	 * @return void
	 * @access private
	 */
	private function submit_forums()
	{
		// Set the options the user configured
		$forum_enable = $this->request->variable('forum_enable', [0 => 0]);
		$forum_enable = array_chunk($forum_enable, 50, true);
		foreach ($forum_enable as $forums_chunk)
		{
			$this->db->sql_transaction('begin');
			foreach ($forums_chunk as $forum_id => $enable)
			{
				$sql = 'UPDATE ' . FORUMS_TABLE . ' SET dark1_rsi_f_enable = ' . (int) $enable . ' WHERE forum_id = ' . (int) $forum_id;
				$this->db->sql_query($sql);
			}
			$this->db->sql_transaction('commit');
		}

		$this->cache->destroy(consts::CACHE_KEY);
	}
}
