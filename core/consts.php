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
 * Reduce Search Index Constants.
 */
class consts
{
	// RSI Forum Flags
	const F_ENABLE_DISABLE	= 0;
	const F_ENABLE_POST		= 1;
	const F_ENABLE_TOPIC	= 2;
	const F_ENABLE_LOCK		= 3;

	/**
	 * RSI Time Format
	 *
	 * Y : Year [1970 - Max Allowed]
	 * m : Month [01 - 12]
	 * d : Day [01 - 28/29/30/31]
	 * h : Hour [01 - 12]
	 * i : Minute [00 - 59]
	 * s : Second [00 - 59]
	 * A : Meridiem [AM/PM]
	 * P : Time Zone [-12:00 - +14:00]
	 *
	 * Example : 1970-01-01 12:00:00 AM +00:00
	**/
	const TIME_FORMAT	= 'Y-m-d h:i:s A P';

	// RSI Cache Key
	const CACHE_KEY		= '_dark1_rsi_search_matrix';
}
