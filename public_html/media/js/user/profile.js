function updatePosition()
{
	var target = $('.queue-pos');
	var positionUrl = target.attr('data-queue-pos-url');
	var enqueueUrl = target.attr('data-queue-add-url');
	var oldTooltip = target.attr('data-tooltip');
	$.get(positionUrl, function(data)
	{
		text = '#' + data.pos;
		if (data.pos)
			target.text(text).wrapInner('<span>');
		else
		{
			target.removeAttr('data-tooltip');
			var updateLink = $('<a href="#">Add to queue</a>');
			updateLink.click(function(e)
				{
					e.preventDefault();
					$.get(enqueueUrl, function(data)
					{
						target.attr('data-tooltip', oldTooltip);
						updatePosition();
					});
				});
			target.html(updateLink);
		}
	});
}

function updateTime()
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

	target.text(text).wrapInner('<span>');
}

$(function()
{
	updatePosition();
	updateTime();
});
