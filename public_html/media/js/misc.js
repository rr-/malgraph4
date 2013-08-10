$.fn.hasAttr = function(name)
{
	return this.attr(name) !== undefined;
};

$(function()
{
	$('.search').submit(function(e)
	{
		var userName = $(this).find('[name=user-name]').val();
		if (userName.replace(/^\s+|\s+$/, '') == '')
		{
			$(this).find('[name=user-name]').val('');
			e.preventDefault();
			e.stopPropagation();
		}
	});
});
