$(function()
{
	var num1 = 8;
	var num2 = 5;

	$('.missing tbody tr').each(function()
	{
		var tr = $(this);
		var ul = tr.find('ul');
		ul.each(function()
		{
			$('<ul class="expand"/>').hide().insertAfter($(this));
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
		newTr.insertAfter(tr).find('td').append(link).hide();
	});

	collapseUls = function()
	{
		$('.tooltip').fadeOut(function()
		{
			$(this).remove();
		});
		$('.missing tbody.tainted tr').each(function()
		{
			var tr = $(this);
			var doExpand = false;
			var all = Math.max.apply(Math, tr.find('td').map(function()
			{
				return $(this).find('li:not(.hidden)').length;
			})) < num1;
			$(this).find('td').each(function()
			{
				var td = $(this);
				var ul1 = td.find('ul:first');
				var ul2 = td.find('ul.expand');

				var index = 0;
				td.find('li').each(function()
				{
					var li = $(this);
					if (index < num2 || all)
					{
						var justAppeared = li.parents('ul').hasClass('expand') && li.is(':not(:visible)');
						ul1.append(li);
						if (justAppeared)
							li.hide().slideDown();
					}
					else
					{
						ul2.append(li);
					}
					if (!li.hasClass('hidden'))
					{
						index ++;
					}
				});
				if (ul2.find('li').length > 0)
				{
					doExpand = true;
				}
			});
			if (tr.find('.proposed li:not(.hidden)').length == 0)
			{
				doExpand = false;
			}

			if (doExpand)
			{
				tr.next().find('td').slideDown();
			}
			else
			{
				tr.next().find('td').slideUp();
			}
		});
		$('.missing tbody.tainted').removeClass('tainted');
	}
	$('.missing tbody').addClass('tainted');
	collapseUls();



	var hide = function(target, fast)
	{
		var prevState = $.fx.off;
		if (fast)
		{
			$.fx.off = true;
		}
		target.addClass('hidden');
		target.slideUp(function()
		{
			var tr = target.parents('tr');
			var td = target.parents('td');
			var ul = target.parents('ul');
			target.hide();
			if (ul.find('li:not(.hidden)').length == 0)
			{
				tr.find('td').slideUp('fast');
			}
			tr.parents('tbody').addClass('tainted');
			collapseUls();
		});
		hidden = typeof(localStorage.hidden) !== 'undefined'
			? JSON.parse(localStorage.hidden)
			: [];
		$('.missing .undelete-msg strong').text(hidden.length);
		$('.missing .undelete-msg').slideDown();
		$.fx.off = prevState;
	}

	$('.missing .delete-trigger').click(function(e)
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
			hide($(this).parents('li'), false);
		}
		e.preventDefault();
	});

	$('.missing .undelete-trigger').click(function(e)
	{
		if (typeof(Storage) === 'undefined')
		{
			alert('Sorry, but local storage is disabled. Can\'t hide.');
		}
		else
		{
			localStorage.removeItem('hidden');
			$('.missing .undelete-msg').slideUp(function()
			{
				$('.missing li.hidden').each(function()
				{
					$(this).parents('tbody').addClass('tainted');
					$(this).parents('tr').find('td').slideDown();
					$(this).slideDown();
				});
				$('.missing li.hidden').removeClass('hidden');
				collapseUls();
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
				var target = $('#' + key);
				hide(target, true);
			}
		}
	}
});
