$(function()
{
	var target = $('.updated');
	var now = new Date();
	var then = new Date(target.attr('data-date'));
	var diff = now - then;
	diff /= 1000.0;
	var text = '';
	if (diff < 60)
	{
		text = 'just now';
	}
	else if (diff < 3600)
	{
		text = (diff / 60).toFixed(0) + ' minutes ago';
	}
	else if (diff < 86400)
	{
		text = (diff / 3600).toFixed(1) + ' hours ago';
	}
	else
	{
		text = (diff / 86400).toFixed(1) + ' days ago';
	}

	var url = target.attr('data-queue-pos-url');
	$.get(url, function(data)
	{
		text += ', #' + data.pos;
		target.text(text);
	});
});
