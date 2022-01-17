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
 * Migration stage 003 : N/A
 */
class rsi_003 extends migration
{
	static public function depends_on()
	{
		return ['\dark1\reducesearchindex\migrations\rsi_002'];
	}

	public function update_data()
	{
		return [
			['config.add', ['dark1_rsi_ign_com_enable', 0]],
			['config_text.add', ['dark1_rsi_ign_com_words', $this->strIgnoreCommonWords()]],
		];
	}

	/**
	 * Ignore Common Words each on new line as a string
	 *
	 * @return string
	 * @access private
	 */
	private function strIgnoreCommonWords()
	{
		$common_words_ary = [
			'a', 'about', 'all', 'an', 'and', 'any', 'are', 'as', 'at', 'be', 'been', 'but', 'by',
			'call', 'can', 'come', 'could', 'did', 'do', 'down', 'each', 'find', 'first', 'for', 'from', 'get', 'go',
			'had', 'has', 'have', 'he', 'her', 'him', 'his', 'how', 'i', 'if', 'in', 'into', 'is', 'it', 'its',
			'like', 'long', 'look', 'made', 'make', 'many', 'may', 'more', 'my', 'no', 'not', 'now', 'none', 'number',
			'of', 'on', 'or', 'other', 'out', 'part', 'people', 'said', 'see', 'she', 'so', 'some',
			'than', 'that', 'the', 'their', 'them', 'then', 'there', 'these', 'they', 'this', 'to', 'up', 'use',
			'was', 'way', 'we', 'were', 'what', 'when', 'which', 'who', 'will', 'with', 'would', 'you', 'your',
		];
		return implode("\n", $common_words_ary);
	}
}
