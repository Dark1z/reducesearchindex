<?php
/**
 *
 * Reduce Search Index [RSI]. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2020-2021, Dark❶, https://dark1.tech
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 *
 * Language : English [en]
 * Translators :
 * 1. Dark❶ [dark1]
 *
 *
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = [];
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, [
	// Common
	'ACP_RSI_SET'		=> 'Settings',
	'ACP_RSI_BY'		=> 'By',
	'ACP_RSI_DAYS'		=> 'Day(s)',
	'ACP_RSI_FORMAT'	=> 'In Format',

	// Main
	'ACP_RSI_ENABLE'				=> 'Reduce Search Index Enable',
	'ACP_RSI_ENABLE_EXPLAIN'		=> 'Enables the Reduce Search Index.<br>Default : No',
	'ACP_RSI_TIME'					=> 'Reduce Time',
	'ACP_RSI_TIME_EXPLAIN'			=> 'The Time from which Search Index is Kept,<br>Also Time before which Search Index is Deleted.<br>Use Date & Time Picker.<br>Default : "1970-01-01 12:00:00 AM +00:00"',
	'ACP_RSI_INTERVAL'				=> 'Reduce Interval',
	'ACP_RSI_INTERVAL_EXPLAIN'		=> 'Interval to Update “Reduce Time” when “Auto Reduce Sync” is run.<br>Default : 365 Day(s)',

	// Forum
	'ACP_RSI_FORUM_EXPLAIN'			=> 'Select the Option to Enable Reduce Search Index for each Forum.<br>Need to select at-least one Forum.<br>Default : <b>Disable</b><br>Following are Options',
	'ACP_RSI_TABLE_FORUM_NAME'		=> 'Forum Name',
	'ACP_RSI_TABLE_FORUM_OPTION'	=> 'Forum Options',
	'ACP_RSI_TABLE_LOCK'			=> 'Topic + Lock',
	'ACP_RSI_TABLE_LOCK_EXPLAIN'	=> 'Search Index from the Forum for the Topic is Deleted and Locked as per the “Reduce Time”.',
	'ACP_RSI_TABLE_TOPIC'			=> 'Topic Only',
	'ACP_RSI_TABLE_TOPIC_EXPLAIN'	=> 'Search Index from the Forum for the Topic is Deleted as per the “Reduce Time”.',
	'ACP_RSI_TABLE_POST'			=> 'Post Only',
	'ACP_RSI_TABLE_POST_EXPLAIN'	=> 'Search Index from the Forum for the Post is Deleted as per the “Reduce Time”.',
	'ACP_RSI_TABLE_DISABLE'			=> 'Disable',

	// Cron
	'ACP_RSI_CRON_ENABLE'			=> 'Enable Auto Reduce Sync',
	'ACP_RSI_CRON_INTERVAL'			=> 'Auto Reduce Sync Interval',
	'ACP_RSI_CRON_LAST_RUN'			=> 'Reduce Sync Last Run',
	'ACP_RSI_CRON_NEXT_RUN'			=> 'Reduce Sync Next Run',
	'ACP_RSI_CRON_RUN'				=> 'Run Reduce Sync',
	'ACP_RSI_CRON_RUN_NOW'			=> 'Run Now',
]);
