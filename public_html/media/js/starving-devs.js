function hide(text)
{
	$('#adblock-enabled ul').slideUp();
	$('#adblock-enabled p').fadeOut('fast', function()
	{
		$(this).html(text).fadeIn(function()
		{
			window.setTimeout(function()
			{
				$('#adblock-enabled').slideUp();
			}, 1337);
		});
	});
}

function show(text)
{
	$('#adblock-enabled p').html(text);
	$('#adblock-enabled').show();
}

$(function()
{
	if (!$('#google-ads').is(':visible') && $.cookie('ads-hidden') === undefined)
	{
		if ($.cookie('ads-promise') !== undefined)
		{
			if (Date.now() - $.cookie('ads-promise') > 24 * 60 * 60 * 1000)
			{
				show('You still haven\'t enabled ads on this site. I thought we were friends&hellip;', 0);
			}
		}
		else
		{
			show();
		}
	}

	//adblockHideUrl defined in ads.phtml
	$('[data-action="agree-auto"]').attr('href', adblockHideUrl).click(function(e)
	{
		hide();
	});

	$('[data-action="agree-manual"]').click(function(e)
	{
		e.preventDefault();
		hide('Remember. You promised!');
		$.cookie('ads-promise', Date.now(), { path: '/', expires: 14 });
	});

	$('[data-action="disagree"]').click(function(e)
	{
		e.preventDefault();
		hide('Sure thing, boss&hellip;');
		$.removeCookie('ads-promise');
		$.cookie('ads-hidden', true, { path: '/', expires: 365 });
	});
});
