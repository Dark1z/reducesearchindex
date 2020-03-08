<?php
/**
 *
 * Reduce Search Index [RSI]. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2020, Dark❶, https://dark1.tech
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace dark1\reducesearchindex\migrations;

use phpbb\db\migration\migration;

class rsi_001_install extends migration
{

	static public function depends_on()
	{
		return array( '\dark1\reducesearchindex\migrations\rsi_000_main');
	}

	public function update_schema()
	{
		return array(
			'add_columns'	=> array(
				$this->table_prefix . 'forums'	=> array(
					'dark1_rsi_f_enable'	=> array('TINT:1', 0),
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_columns'	=> array(
				$this->table_prefix . 'forums'	=> array(
					'dark1_rsi_f_enable',
				),
			),
		);
	}

	public function update_data()
	{
		return array(
			// Config
			array('config.add', array('dark1_rsi_enable', 0)),
			array('config.add', array('dark1_rsi_time', 0, true)),
			array('config.add', array('dark1_rsi_interval', 31536000, true)),
			array('config.add', array('dark1_rsi_auto_reduce_sync_enable', 0, true)),
			array('config.add', array('dark1_rsi_auto_reduce_sync_gc', 864000, true)),
			array('config.add', array('dark1_rsi_auto_reduce_sync_last_gc', 0, true)),

			// Module
			array('module.add', array(
				'acp',
				'ACP_RSI_TITLE',
				array(
					'module_basename'	=> '\dark1\reducesearchindex\acp\main_module',
					'modes'				=> array('main', 'forum', 'cron'),
				),
			)),
		);
	}
}
