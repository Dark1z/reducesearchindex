<?php
/**
 *
 * Reduce Search Index [RSI]. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2020, Dark❶, https://dark1.tech
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace dark1\reducesearchindex;

/**
 * @ignore
 */
use phpbb\extension\base;

/**
 * Reduce Search Index Extension base
 */
class ext extends base
{
	/** string Require phpBB v3.2.9 due to various reasons. */
	const RSI_PHPBB_MIN_VERSION = '3.2.9';

	/**
	 * {@inheritdoc}
	 */
	public function is_enableable()
	{
		$config = $this->container->get('config');
		return phpbb_version_compare($config['version'], self::RSI_PHPBB_MIN_VERSION, '>=') && phpbb_version_compare(PHPBB_VERSION, self::RSI_PHPBB_MIN_VERSION, '>=');
	}

}
