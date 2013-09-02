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

function getProcessedDate()
{
	return new Date(Date.parse($('#processed').val()));
}

function ucfirst(str)
{
	str += '';
	var f = str.charAt(0).toUpperCase();
	return f + str.substr(1);
}

function lpad(str, width)
{
	str += '';
	return str.length >= width
		? str
		: new Array(width - str.length + 1).join('0') + str;
}
