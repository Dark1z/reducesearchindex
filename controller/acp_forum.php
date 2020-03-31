<?php
/**
 *
 * Reduce Search Index [RSI]. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2020, Dark❶, https://dark1.tech
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace dark1\reducesearchindex\controller;

/**
 * @ignore
 */
use phpbb\language\language;
use phpbb\log\log;
use phpbb\request\request;
use phpbb\template\template;
use phpbb\user;
use phpbb\db\driver\driver_interface as db_driver;
use phpbb\cache\driver\driver_interface as cache_driver;

/**
 * Reduce Search Index [RSI] ACP controller Forum.
 */
class acp_forum extends acp_base
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\cache\driver\driver_interface */
	protected $cache;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\config\config					$config			Config object
	 * @param \phpbb\language\language				$language		Language object
	 * @param \phpbb\log\log						$log			Log object
	 * @param \phpbb\request\request				$request		Request object
	 * @param \phpbb\template\template				$template		Template object
	 * @param \phpbb\user							$user			User object
	 * @param \phpbb\db\driver\driver_interface		$db				Database object
	 * @param \phpbb\cache\driver\driver_interface	$cache			Cache object
	 */
	public function __construct(language $language, log $log, request $request, template $template, user $user, db_driver $db, cache_driver $cache)
	{
		parent::__construct($language, $log, $request, $template, $user);

		$this->db		= $db;
		$this->cache	= $cache;
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
		$forums = [];
		$sql = 'SELECT forum_id, forum_type, forum_name, parent_id, left_id, right_id, dark1_rsi_f_enable FROM ' . FORUMS_TABLE . ' ORDER BY left_id ASC';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$forums[] = $row;
		}
		$this->db->sql_freeresult($result);

		$right = 0;
		$padding_store = array('0' => '');
		$padding = '';

		foreach ($forums as $row)
		{
			$tpl_row = [];

			if ($row['left_id'] < $right)
			{
				$padding .= '&nbsp; &nbsp; &nbsp;';
				$padding_store[$row['parent_id']] = $padding;
			}
			else if ($row['left_id'] > $right + 1)
			{
				$padding = (isset($padding_store[$row['parent_id']])) ? $padding_store[$row['parent_id']] : '';
			}
			$right = $row['right_id'];

			// Category forums are displayed for organizational purposes, but have no configuration
			if ($row['forum_type'] == FORUM_CAT)
			{
				$tpl_row = [
					'S_IS_CAT'		=> true,
					'FORUM_NAME'	=> $padding . '&nbsp; &#8627; &nbsp;' . $row['forum_name'],
				];
			}
			// Normal forums have a radio input with the value selected based on the value of the setting
			else if ($row['forum_type'] == FORUM_POST)
			{
				// The labels for all the inputs are constructed based on the forum IDs to make it easy to know which
				$tpl_row = [
					'S_IS_CAT'		=> false,
					'FORUM_NAME'	=> $padding . '&nbsp; &#8627; &nbsp;' . $row['forum_name'],
					'FORUM_ID'		=> $row['forum_id'],
					'ENABLE'		=> $row['dark1_rsi_f_enable'],
				];
			}
			// Other forum types (links) are ignored

			if (!empty($tpl_row))
			{
				$this->template->assign_block_vars('forumrow', $tpl_row);
			}
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
		$sql = 'SELECT forum_id, forum_type FROM ' . FORUMS_TABLE . ' ORDER BY left_id ASC';
		$result = $this->db->sql_query($sql);
		$forum_id_set = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			if ($row['forum_type'] == FORUM_POST)
			{
				$forum_id_set[$row['forum_id']] =  $this->request->variable('forum_' . $row['forum_id'] . '_enable', 0);
			}
		}
		$this->db->sql_freeresult($result);
		foreach ($forum_id_set as $id => $input)
		{
			$sql = 'UPDATE ' . FORUMS_TABLE . ' SET dark1_rsi_f_enable = ' . (int) $input . ' WHERE forum_id = ' . (int) $id;
			$this->db->sql_query($sql);
		}

		$this->cache->destroy('_dark1_rsi_search_matrix');
	}
}
