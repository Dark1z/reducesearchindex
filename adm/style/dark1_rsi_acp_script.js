/**
 *
 * Reduce Search Index [RSI]. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2020, Dark❶, https://dark1.tech
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

var TimeConv = new AnyTime.Converter({
	format: TimeFormat
});

if (mode === 'main')
{
	var options = {
		format: TimeFormat,
		latest: TimeConv.format(TimeConv.parse(TimeLatest))
	};
	$('#dark1_rsi_time').click(function (e)
	{
		$(this).off('click').AnyTime_picker(options).focus();
	}).keydown(function (e)
	{
		var key = e.keyCode || e.which;
		if ((key != 16) && (key != 9))
		{ // shift, del, tab
			$(this).off('keydown').AnyTime_picker(options).focus();
			e.preventDefault();
		}
	});
}

if (mode === 'cron')
{
	var options_auto_ls = {
		format: TimeFormat,
		earliest: TimeConv.format(TimeConv.parse(EarliestTimeAuto))
	};
	$('#dark1_rsi_auto_reduce_sync_last_gc').click(function (e)
	{
		$(this).off('click').AnyTime_picker(options_auto_ls).focus();
	}).keydown(function (e)
	{
		var key = e.keyCode || e.which;
		if ((key != 16) && (key != 9))
		{ // shift, del, tab
			$(this).off('keydown').AnyTime_picker(options_auto_ls).focus();
			e.preventDefault();
		}
	});
}
