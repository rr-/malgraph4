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



	$('.missing .delete').click(function(e)
	{
		var key = $(this).parents('li').attr('id');
		if (typeof(Storage) === 'undefined')
		{
			alert('Sorry, but local storage is disabled. Can\'t hide.');
		}
		else
		{
			hidden = typeof(localStorage.hidden) !== 'undefined'
				? JSON.parse(localStorage.hidden)
				: [];
			hidden.push(key);
			hidden = hidden.filter(function(el,index,arr)
			{
				return index == arr.indexOf(el);
			});
			localStorage.hidden = JSON.stringify(hidden);
			$(this).parents('li').fadeOut(function()
			{
				var p = $(this).parents('td');
				$(this).hide();
				if (p.find('li:visible').length == 0)
				{
					p.parents('tr').find('td').slideUp('fast');
				}
			});
			$('.missing .undelete strong').text(hidden.length);
			$('.missing .undelete').slideDown();
		}
		e.preventDefault();
	});

	$('.missing .undelete a').click(function(e)
	{
		if (typeof(Storage) === 'undefined')
		{
			alert('Sorry, but local storage is disabled. Can\'t hide.');
		}
		else
		{
			localStorage.removeItem('hidden');
			$('.missing .undelete').slideUp(function()
			{
				$('.missing td:not(:visible), .missing li:not(:visible)').slideDown();
			});
		}
		e.preventDefault();
	});

	if (typeof(Storage) !== 'undefined' && typeof(localStorage.hidden) !== 'undefined')
	{
		var hidden = JSON.parse(localStorage.hidden);
		if (hidden.length)
		{
			for (var i in hidden)
			{
				var key = hidden[i];
				var p = $('#' + key).parents('td');
				$('#' + key).hide();
				if (p.find('li:visible').length == 0)
				{
					p.parents('tr').hide();
				}
			}
			$('.missing .undelete strong').text(hidden.length);
			$('.missing .undelete').show();
		}
	}
});
