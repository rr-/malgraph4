function toggleEntries(target, data, ajax)
{
	var resetHeight = function()
	{
		$('body').css('min-height', 'auto');
	}

	if (typeof ajax == 'undefined')
	{
		ajax = true;
	}
	var url = '/' + $('#user-name').val() + '/entries,' + $('#media').val();
	var uniqueId = JSON.stringify(data);
	if (target.data('unique-id') == uniqueId)
	{
		if (target.is(':visible'))
		{
			target.stop(true, true).slideUp('fast', resetHeight);
		}
		else
		{
			target.stop(true, true).slideDown();
		}
		return;
	}

	$('body').css('min-height', $('body').height() + 'px');

	target.data('unique-id', uniqueId);
	target.slideUp('fast', function()
	{
		if (ajax)
		{
			$.get(url, data, function(response)
			{
				target.html(response);
				target.stop(true, true).slideDown('medium', resetHeight);
			});
		}
		else
		{
			target.stop(true, true).slideDown('medium', resetHeight);
		}
	});
}

$(function()
{
	$('.entries-wrapper').on('click', '.close', function(event)
	{
		var target = $(this).parents('.entries-wrapper');
		if ($(target).hasClass('singular'))
		{
			target = $(this).parents('.entries-wrapper');
		}
		target.stop(true, true).slideUp('fast');
		event.preventDefault();
	});
});
