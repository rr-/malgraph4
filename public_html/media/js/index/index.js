var lastNum = 0;

function switchShowcaseTab(num)
{
	$('#showcase menu li').removeClass('active');
	$('#showcase menu li').eq(num).addClass('active');
	if (lastNum == num)
	{
		return;
	}
	lastNum = num;
	$('#showcase .tab').slideUp('fast');
	$('#showcase .tab').eq(num).slideDown('fast');
}

$(function()
{
	$('#main .search input').focus();

	$('#showcase menu li').each(function(i, index)
	{
		$(this).hover(function()
		{
			switchShowcaseTab(i);
		}).click(function(e)
		{
			e.preventDefault();
		});
	});

	switchShowcaseTab(0);
});
