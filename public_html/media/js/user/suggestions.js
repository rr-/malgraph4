$(function()
{
	$('.missing tbody tr').each(function()
	{
		var num1 = 8;
		var num2 = 5;

		var tr = $(this);
		var ul = tr.find('ul');
		var doCollapse = Math.max.apply(Math, tr.find('ul').map(function()
		{
			return $(this).find('li').length;
		})) > num1;

		if (doCollapse)
		{
			ul.each(function()
			{
				var ul2 = $('<ul class="expand"/>');
				$(this).find('li').each(function(i)
				{
					if (i > num2)
					{
						ul2.append($(this));
					}
				});
				ul2.insertAfter($(this)).hide();
			});
			var newTr = $('<tr><td colspan="2"/></tr>');
			var link = $('<a class="more" href="#">(more)</a>').click(function(e)
			{
				e.preventDefault();
				tr.find('.expand').slideDown(function()
				{
					link.slideUp();
				});
			});
			newTr.insertAfter(tr).find('td').append(link);
		}
	});
});
