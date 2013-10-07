var lastNum = 0;

function switchShowcaseTab(num)
{
	$('#showcase nav li').removeClass('active');
	$('#showcase nav li').eq(num).addClass('active');
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

	$('#showcase nav li').each(function(i, index)
	{
		$(this).click(function(e)
		{
			switchShowcaseTab(i);
			e.preventDefault();
		});
	});

	function nextShowcaseTab()
	{
		var num = $('#showcase nav li.active').index();
		num ++;
		num %= $('#showcase nav li').length;
		switchShowcaseTab(num);
	}

	window.setInterval(nextShowcaseTab, 5000);

	switchShowcaseTab(0);
});
