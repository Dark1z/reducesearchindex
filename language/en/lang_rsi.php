<?php
/**
 *
 * Reduce Search Index [RSI]. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2020, Dark❶, https://dark1.tech
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
	$lang = array();
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

$lang = array_merge($lang, array(
	// phpBB Log
	'ACP_RSI_LOG_SET_SAV'	=> '<strong>Reduce Search Index [RSI]</strong><br>» %s saved successfully!',
	'RSI_AUTO_LOG'			=> '<strong>Reduce Search Index [RSI]</strong><br>» Auto Reduce Updater completed.<br>» Search Restricted for Last “%1$s” Day(s) from “%2$s”',

	// Search Notice
	'RSI_NOTICE'			=> 'Search Notice',
	'RSI_NOTICE_TEXT'		=> 'The search results are restricted and only available from',

));
