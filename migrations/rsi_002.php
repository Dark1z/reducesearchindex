<?php
/**
 *
 * Reduce Search Index [RSI]. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2020-2021, Dark❶, https://dark1.tech
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace dark1\reducesearchindex\migrations;

/**
 * @ignore
 */
use phpbb\db\migration\migration;

/**
 * Migration stage 002 : N/A
 */
class rsi_002 extends migration
{
	static public function depends_on()
	{
		return ['\dark1\reducesearchindex\migrations\rsi_001_install'];
	}

	public function update_data()
	{
		return [
			// Remove Config if Exist
			['if', [
				isset($this->config['dark1_rsi']),
				['config.remove', ['dark1_rsi']],
			]],
		];
	}
}
