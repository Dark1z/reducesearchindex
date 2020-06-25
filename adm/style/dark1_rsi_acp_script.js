/**
 *
 * Reduce Search Index [RSI]. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2020, Dark❶, https://dark1.tech
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

(function($) {  // Avoid conflicts with other libraries

	'use strict';

	let TimeConv = new AnyTime.Converter({
		format: RSI_TimeFormat
	});

	let options = {
		format: RSI_TimeFormat,
		latest: TimeConv.format(TimeConv.parse(RSI_TimeLatest))
	};

	$('#dark1_rsi_time').click(function (e)
	{
		$(this).off('click').AnyTime_picker(options).focus();
	}).keydown(function (e)
	{
		let key = e.keyCode || e.which;
		if ((key != 16) && (key != 9))
		{ // shift, del, tab
			$(this).off('keydown').AnyTime_picker(options).focus();
			e.preventDefault();
		}
	});

})(jQuery); // Avoid conflicts with other libraries
