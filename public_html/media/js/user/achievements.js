$(function()
{
	$('.more-trigger').click(function(e)
	{
		var target = $(this).parents('.achi-entry').find('.entries-wrapper');
		toggleEntries(target, [], false);
		e.preventDefault();
	});
});
