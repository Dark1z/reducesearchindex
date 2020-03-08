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
use phpbb\config\config;
use phpbb\db\driver\driver_interface;
use phpbb\template\template;
use phpbb\user;
use phpbb\language\language;
use phpbb\log\log;
use phpbb\request\request;
use phpbb\cron\manager as cron_manager;

/**
 * Reduce Search Index [RSI] ACP controller.
 */
class acp_controller
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\log\log */
	protected $log;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\cron\manager */
	protected $cron_manager;

	/** @var string The module ID */
	protected $id;

	/** @var string The module mode */
	protected $mode;

	/** @var string Custom form action */
	protected $u_action;

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
	 * @param \phpbb\cron\manager					$cron_manager	Cron manager
	 */
	public function __construct(config $config, language $language, log $log, request $request, template $template, user $user, driver_interface $db, cron_manager $cron_manager)
	{
		$this->config		= $config;
		$this->language		= $language;
		$this->log			= $log;
		$this->request		= $request;
		$this->template		= $template;
		$this->user			= $user;
		$this->db			= $db;
		$this->cron_manager	= $cron_manager;
	}

	/**
	 * Set Data form.
	 *
	 * @param int		$id			The module ID
	 * @param string	$mode		The module mode
	 * @param string	$u_action	Custom form action
	 *
	 * @return void
	 */
	public function set_data($id, $mode, $u_action)
	{
		$this->id = $id;
		$this->mode = strtolower($mode);
		$this->u_action = $u_action;
	}

	/**
	 * Get Data form.
	 *
	 * @return array Having keys 'tpl_name' & 'page_title'
	 */
	public function get_data()
	{
		return [
			'tpl_name' => 'dark1_rsi_acp_' . $this->mode,
			'page_title' => $this->language->lang('ACP_RSI_TITLE') . ' - ' . $this->language->lang('ACP_RSI_' . strtoupper($this->mode)),
		];
	}

	/**
	 * Set Display form.
	 *
	 * @return void
	 */
	public function display()
	{
		$ext_name_rsi = 'Reduce Search Index [RSI]';
		$ext_by_dark1 = 'Dark❶ [dark1]';

		// Add our common language file
		$this->language->add_lang('lang_rsi', 'dark1/reducesearchindex');

		// Create a form key for preventing CSRF attacks
		add_form_key('dark1_rsi_acp_' . $this->mode);

		// Set u_action in the template
		$this->template->assign_vars([
			'U_ACTION'		=> $this->u_action,
			'RSI_MODE'		=> $this->mode,
			'RSI_EXT_MODE'	=> $this->language->lang('ACP_RSI_' . strtoupper($this->mode)),
			'RSI_EXT_NAME'	=> $ext_name_rsi,
			'RSI_EXT_DEV'	=> $ext_by_dark1,
		]);

		$mode_display = 'mode_' . $this->mode;

		if (!method_exists($this, $mode_display))
		{
			trigger_error('FORM_INVALID', E_USER_WARNING);
		}

		// Trigger the Mode
		$this->$mode_display();
	}

	/**
	 * Check Form On Submit .
	 *
	 * @return void
	 */
	private function check_form_on_submit()
	{
		// Test if the submitted form is valid
		if (!check_form_key('dark1_rsi_acp_' . $this->mode))
		{
			trigger_error('FORM_INVALID', E_USER_WARNING);
		}
	}

	/**
	 * Success Form On Submit.
	 * Used to Log & Trigger Success Err0r.
	 *
	 * @return void
	 */
	private function success_form_on_submit()
	{
		// Add option settings change action to the admin log
		$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'ACP_RSI_LOG_SET_SAV', time(), array($this->language->lang('ACP_RSI_' . strtoupper($this->mode))));

		// Option settings have been updated and logged
		// Confirm this to the user and provide link back to previous page
		trigger_error($this->language->lang('ACP_RSI_LOG_SET_SAV', $this->language->lang('ACP_RSI_' . strtoupper($this->mode))) . adm_back_link($this->u_action), E_USER_NOTICE);
	}

	/**
	 * Display the options a user can configure for Main Mode.
	 *
	 * @return void
	 */
	private function mode_main()
	{
		// Is the form being submitted to us?
		if ($this->request->is_set_post('submit'))
		{
			$this->check_form_on_submit();

			// Set the options the user configured
			$this->config->set('dark1_rsi_enable', $this->request->variable('dark1_rsi_enable', 0));
			$this->config->set('dark1_rsi_time', strtotime($this->request->variable('dark1_rsi_time', '0', true)));
			$this->config->set('dark1_rsi_interval', ($this->request->variable('dark1_rsi_interval', 0)) * 86400);

			$this->success_form_on_submit();
		}

		// Set output variables for display in the template
		$this->template->assign_vars([
			'RSI_ENABLE'		=> $this->config['dark1_rsi_enable'],
			'RSI_INTERVAL'		=> ($this->config['dark1_rsi_interval'] / 86400),
			'RSI_TIME'			=> $this->user->format_date($this->config['dark1_rsi_time'], 'Y-m-d h:i:s A P', true),
			'RSI_CURR_TIME'		=> $this->user->format_date(time(), 'Y-m-d h:i:s A P', true),
		]);
	}

	/**
	 * Display the options a user can configure for Forum Mode.
	 *
	 * @return void
	 */
	private function mode_forum()
	{
		// Is the form being submitted to us?
		if ($this->request->is_set_post('submit'))
		{
			$this->check_form_on_submit();

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

			$this->success_form_on_submit();
		}

		// Set output variables for display in the template
		$forum_rows = $this->print_forums();
		foreach ($forum_rows as $key => $tpl_row)
		{
			$this->template->assign_block_vars('forumrow', $tpl_row);
		}
	}

	/**
	 * Display the Forum options.
	 *
	 * @return array
	 */
	private function print_forums()
	{
		$sql = 'SELECT forum_id, forum_type, forum_name, parent_id, left_id, right_id, dark1_rsi_f_enable FROM ' . FORUMS_TABLE . ' ORDER BY left_id ASC';
		$result = $this->db->sql_query($sql);

		$right = 0;
		$padding_store = array('0' => '');
		$padding = '';
		$forum_rows = array();

		while ($row = $this->db->sql_fetchrow($result))
		{
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
				$tpl_row = array(
					'S_IS_CAT'		=> true,
					'FORUM_NAME'	=> $padding . '&nbsp; &#8627; &nbsp;' . $row['forum_name'],
				);
				$forum_rows[] = $tpl_row;
			}
			// Normal forums have a radio input with the value selected based on the value of the discord_notifications_enabled setting
			else if ($row['forum_type'] == FORUM_POST)
			{
				// The labels for all the inputs are constructed based on the forum IDs to make it easy to know which
				$tpl_row = array(
					'S_IS_CAT'		=> false,
					'FORUM_NAME'	=> $padding . '&nbsp; &#8627; &nbsp;' . $row['forum_name'],
					'FORUM_ID'		=> $row['forum_id'],
					'ENABLE'		=> $row['dark1_rsi_f_enable'],
				);
				$forum_rows[] = $tpl_row;
			}
			// Other forum types (links) are ignored
		}
		$this->db->sql_freeresult($result);

		return $forum_rows;
	}

	/**
	 * Display the options a user can configure for Cron Mode.
	 *
	 * @return void
	 */
	private function mode_cron()
	{
		// Is the form being submitted to us?
		if ($this->request->is_set_post('submit'))
		{
			$this->check_form_on_submit();

			// Set the options the user configured
			$this->config->set('dark1_rsi_auto_reduce_sync_enable', $this->request->variable('dark1_rsi_auto_reduce_sync_enable', 0));
			$this->config->set('dark1_rsi_auto_reduce_sync_gc', ($this->request->variable('dark1_rsi_auto_reduce_sync_gc', 0)) * 86400);
			$this->config->set('dark1_rsi_auto_reduce_sync_last_gc', strtotime($this->request->variable('dark1_rsi_auto_reduce_sync_last_gc', '0', true)), false);

			$this->success_form_on_submit();
		}

		// Run Cron Task
		if ($this->request->is_set_post('runcrontask'))
		{
			$this->check_form_on_submit();

			$cron_task = $this->cron_manager->find_task('dark1.reducesearchindex.cron.auto_reduce_sync');
			$cron_task->run();
			$this->template->assign_var('DONE_RUN_LS_CRON', (string) true);
		}

		// Set output variables for display in the template
		$this->template->assign_vars([
			'ENABLE_CRON'		=> $this->config['dark1_rsi_auto_reduce_sync_enable'],
			'CRON_INTERVAL'		=> ($this->config['dark1_rsi_auto_reduce_sync_gc'] / 86400),
			'CRON_LAST_RUN'		=> $this->user->format_date($this->config['dark1_rsi_auto_reduce_sync_last_gc'], 'Y-m-d h:i:s A P', true),
			'CRON_NEXT_RUN'		=> $this->user->format_date($this->config['dark1_rsi_auto_reduce_sync_last_gc'] + $this->config['dark1_rsi_auto_reduce_sync_gc'], 'Y-m-d h:i:s A P', true),
			'CRON_PREV_RUN'		=> $this->user->format_date($this->config['dark1_rsi_auto_reduce_sync_last_gc'] - $this->config['dark1_rsi_auto_reduce_sync_gc'], 'Y-m-d h:i:s A P', true),
		]);
	}
}
