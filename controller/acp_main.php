<?php
/**
 *
 * Reduce Search Index [RSI]. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2020-forever, Dark❶, https://dark1.tech
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
use phpbb\config\config;
use phpbb\config\db_text as config_text;

/**
 * Reduce Search Index [RSI] ACP controller Main.
 */
class acp_main extends acp_base
{
	/** @var config */
	protected $config;

	/** @var config_text */
	protected $config_text;

	/**
	 * Constructor.
	 *
	 * @param language		$language		Language object
	 * @param log			$log			Log object
	 * @param request		$request		Request object
	 * @param template		$template		Template object
	 * @param user			$user			User object
	 * @param config		$config			Config object
	 * @param config_text	$config_text	Config text object
	 */
	public function __construct(language $language, log $log, request $request, template $template, user $user, config $config, config_text $config_text)
	{
		parent::__construct($language, $log, $request, $template, $user);

		$this->config		= $config;
		$this->config_text	= $config_text;
	}

	/**
	 * Display the options a user can configure for Main Mode.
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

			// Set the options the user configured
			$this->config->set('dark1_rsi_enable', $this->request->variable('dark1_rsi_enable', 0));
			$this->config->set('dark1_rsi_ign_com_enable', $this->request->variable('dark1_rsi_ign_com_enable', 0));
			$this->config->set('dark1_rsi_time', strtotime($this->request->variable('dark1_rsi_time', '0', true)));
			$this->config->set('dark1_rsi_interval', $this->request->variable('dark1_rsi_interval', 0) * 86400);

			$unq_com_words = explode("\n", $this->request->variable('dark1_rsi_ign_com_words', '', true));
			$unq_com_words = array_unique(array_map('strtolower', $unq_com_words));
			natsort($unq_com_words);
			$this->config_text->set('dark1_rsi_ign_com_words', implode("\n", $unq_com_words));

			$this->success_form_on_submit();
		}

		// Set output variables for display in the template
		$this->template->assign_vars([
			'RSI_ENABLE'			=> $this->config['dark1_rsi_enable'],
			'RSI_IGN_COM_ENABLE'	=> $this->config['dark1_rsi_ign_com_enable'],
			'RSI_INTERVAL'			=> $this->config['dark1_rsi_interval'] / 86400,
			'RSI_TIME'				=> $this->user->format_date($this->config['dark1_rsi_time'], consts::TIME_FORMAT, true),
			'RSI_CURR_TIME'			=> $this->user->format_date(time(), consts::TIME_FORMAT, true),
			'RSI_IGN_COM_WORDS'		=> $this->config_text->get('dark1_rsi_ign_com_words'),
		]);
	}
}
