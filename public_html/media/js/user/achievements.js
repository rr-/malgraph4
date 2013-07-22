$(function()
{
	$('.more-trigger').click(function(e)
	{
		var target = $(this).parents('.achi-entry').find('.entries-wrapper');
		toggleEntries(target, [], false);
		e.preventDefault();
	});
	$('.toggle-hidden').click(function(e)
	{
		var targets = $(this).parents('.section').find('.achi-entry.hidden');
		targets.each(function()
		{
			var target = $(this);
			if (target.is(':hidden'))
			{
				target.show();
				target.css('height', $(this).height());
				target.hide();
				target.slideDown();
			}
			else
			{
				target.slideUp();
			}
		});
		$(this).text($(this).text() == 'show' ? 'hide' : 'show');
		e.preventDefault();
	});
});
