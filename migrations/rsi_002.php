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

class rsi_002 extends migration
{

	static public function depends_on()
	{
		return array('\dark1\reducesearchindex\migrations\rsi_001_install');
	}

	public function update_data()
	{
		return array(
			// Remove Config if Exist
			array('if', array(
				isset($this->config['dark1_rsi']),
				array('config.remove', array('dark1_rsi')),
			)),
		);
	}
}
