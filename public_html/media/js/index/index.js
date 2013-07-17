$(function()
{
	$('#main .search input').focus();
	$('a.about-mal').click(function(event) {
		$('div.about-mal').fadeToggle();
		event.preventDefault();
	});
});
