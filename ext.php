<?php
/**
 *
 * Reduce Search Index [RSI]. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2020-2021, Dark❶, https://dark1.tech
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
	/** @var string Require phpBB v3.2.3 due to phpBB core events. */
	const PHPBB_MIN_3_2_X = '3.2.3';

	/**
	 * {@inheritdoc}
	 */
	public function is_enableable()
	{
		return $this->pbpbb_ver_chk();
	}

	/**
	 * phpBB Version Check.
	 *
	 * @return bool
	 * @access private
	 */
	private function pbpbb_ver_chk()
	{
		$config = $this->container->get('config');

		$phpbb_version = phpbb_version_compare(PHPBB_VERSION, $config['version'], '>=') ? PHPBB_VERSION : $config['version'] ;

		return phpbb_version_compare($phpbb_version, self::PHPBB_MIN_3_2_X, '>=');
	}
}
