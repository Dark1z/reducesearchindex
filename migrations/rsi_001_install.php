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
		return array('\dark1\reducesearchindex\migrations\rsi_000_main');
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
}
