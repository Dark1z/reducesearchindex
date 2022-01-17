<?php
/**
 *
 * Reduce Search Index [RSI]. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2020-forever, Dark❶, https://dark1.tech
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace dark1\reducesearchindex\core;

/**
 * @ignore
 */
use dark1\reducesearchindex\core\forum_map;

/**
 * RSI Forum Mapper.
 */
class forum_map_rsi extends forum_map
{
	/**
	 * Get forum custom SQL Array.
	 *
	 * @param array		$sql_ary	Forum SQL Array
	 *
	 * @return array
	 * @access protected
	 */
	protected function get_forums_cust_sql_ary($sql_ary)
	{
		$sql_ary['SELECT'] .= ', f.dark1_rsi_f_enable';
		return $sql_ary;
	}

	/**
	 * Get forum custom template row.
	 *
	 * @param array		$row	Forum row
	 *
	 * @return array
	 * @access protected
	 */
	protected function get_forum_cust_tpl_row($row)
	{
		$tpl_row = [];
		if ($row['forum_type'] == FORUM_POST)
		{
			$tpl_row = [
				'ENABLE'	=> $row['dark1_rsi_f_enable'],
			];
		}
		return $tpl_row;
	}
}
