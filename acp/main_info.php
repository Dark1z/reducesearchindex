<?php
/**
 *
 * Reduce Search Index [RSI]. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2020, Dark❶, https://dark1.tech
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace dark1\reducesearchindex\acp;

/**
 * Reduce Search Index [RSI] ACP module info.
 */
class main_info
{
	public function module()
	{
		return array(
			'filename'	=> '\dark1\reducesearchindex\acp\main_module',
			'title'		=> 'ACP_RSI_TITLE',
			'modes'		=> array(
				'main'	=> array(
					'title'	=> 'ACP_RSI_MAIN',
					'auth'	=> 'ext_dark1/reducesearchindex && acl_a_board',
					'cat'	=> array('ACP_RSI_TITLE')
				),
				'forum'	=> array(
					'title'	=> 'ACP_RSI_FORUM',
					'auth'	=> 'ext_dark1/reducesearchindex && acl_a_board',
					'cat'	=> array('ACP_RSI_TITLE')
				),
				'cron'	=> array(
					'title'	=> 'ACP_RSI_CRON',
					'auth'	=> 'ext_dark1/reducesearchindex && acl_a_board',
					'cat'	=> array('ACP_RSI_TITLE')
				),
			),
		);
	}
}
