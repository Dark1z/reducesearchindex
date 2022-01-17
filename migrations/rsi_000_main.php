<?php
/**
 *
 * Reduce Search Index [RSI]. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2020-forever, Dark❶, https://dark1.tech
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace dark1\reducesearchindex\migrations;

/**
 * @ignore
 */
use phpbb\db\migration\migration;

/**
 * Migration stage 000 : Main
 */
class rsi_000_main extends migration
{
	static public function depends_on()
	{
		return ['\phpbb\db\migration\data\v320\v320'];
	}

	public function update_data()
	{
		return [
			// Config
			['config.add', ['dark1_rsi_enable', 0]],
			['config.add', ['dark1_rsi_time', 0, true]],
			['config.add', ['dark1_rsi_interval', 31536000, true]],
			['config.add', ['dark1_rsi_auto_reduce_sync_enable', 0, true]],
			['config.add', ['dark1_rsi_auto_reduce_sync_gc', 864000, true]],
			['config.add', ['dark1_rsi_auto_reduce_sync_last_gc', 0, true]],

			// Module
			['module.add', [
				'acp',
				'ACP_CAT_DOT_MODS',
				'ACP_RSI_TITLE',
			]],
			['module.add', [
				'acp',
				'ACP_RSI_TITLE',
				[
					'module_basename'	=> '\dark1\reducesearchindex\acp\main_module',
					'modes'				=> ['main', 'forum', 'cron'],
				],
			]],
		];
	}
}
