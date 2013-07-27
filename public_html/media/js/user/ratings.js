$(function()
{
	$('.export-trigger').click(function(e)
	{
		e.preventDefault();
		var target = $('.export.popup');
		$('.popup-wrapper').fadeIn('fast');
		updatePreview(target);
	});

	function updatePreview(target)
	{
		var img = $(target).find('img');
		var matches = $(target).find('textarea').val().match(/\[img\]([^\[]*)\[\/img]/);
		var newSrc = matches[1] + '&bypass-cache=1';
		if (img.attr('src') != newSrc)
		{
			img.attr('src', newSrc);
		}
	}

	/* prepate type selector */
	var types =
	[
		{ 'type': 1, 'name': 'Anime' },
		{ 'type': 2, 'name': 'Manga' },
		{ 'type': 3, 'name': 'Anime & manga' },
	];
	$('select[name=\'type\']').each(function()
	{
		for (var i in types)
		{
			$(this).append($('<option/>').text(types[i]['name']).data('type', types[i]['type']));
		}
		$(this).change(function()
		{
			var textarea = $(this).parents('.export').find('textarea');
			var type = $(this).find('option:selected').data('type');
			textarea.val(textarea.val().replace(/(type=[0-9]*)/, 'type=' + type));
			updatePreview($(this).parents('.export'));
		});
	});

	/* prepare theme selecton */
	var themes =
	[
		{ 'params': { }, 'name': 'Blue (default)' },
		{ 'params': { 'bar1': '00ffaaaa', 'bar2': '00ee6677', 'line1': 'c0ffaaaa', 'line2': 'c0ee6677', 'back': 'ffffffff', 'font1': '20442233', 'font2': '85aa4444', 'title': '00cc5566', 'logo': '00cc5566' }, 'name': 'Pink' },
		{ 'params': { 'bar1': '0044ff44', 'bar2': '00008800', 'line1': 'dd44ff44', 'line2': 'dd00aa00', 'back': 'ffffffff', 'font1': '20227722', 'font2': '90227722', 'title': '00227722', 'logo': '00227722' }, 'name': 'Green' },
		{ 'params': { 'bar1': '00eecc05', 'bar2': '00dd2200', 'line1': 'aaffdd00', 'line2': 'aaff0000', 'back': 'ffffffff', 'font1': '20220700', 'font2': '90220700', 'title': '00220700', 'logo': '00220700' }, 'name': 'Flame (yellow + red)' },
		{ 'params': { 'bar1': 'aa000000', 'bar2': '33000000', 'line1': 'ff000000', 'line2': 'dd000000', 'back': 'ffffffff', 'font1': '20000000', 'font2': '90000000', 'title': '20000000', 'logo': '20000000' }, 'name': 'Gray' },
		{ 'params': { 'bar1': '0084a0d4', 'bar2': '0003359a', 'line1': '00446084', 'line2': '0001156a', 'back': '00000000', 'font1': '00779fe2', 'font2': '50779fe2', 'title': '00779fe2', 'logo': '00779fe2' }, 'name': 'unBlue (blue on black)' },
		{ 'params': { 'bar1': '0044ff44', 'bar2': '00008800', 'line1': '00004400', 'line2': '00008800', 'back': '00000000', 'font1': '2044ff44', 'font2': '00008800', 'title': '0033aa33', 'logo': '0033aa33' }, 'name': 'Matrix (green on black)' },
		{ 'params': { 'bar1': 'a4c0f4', 'bar2': '13459a', 'line1': 'f8fafe', 'line2': 'eff2f8', 'back': 'ffffff', 'font1': '000000', 'font2': 'aaaaaa', 'title': '577fc2', 'logo': '577fc2' }, 'name': 'Custom' },
	];
	$('select[name=\'theme\']').each(function()
	{
		for (var i in themes)
		{
			$(this).append($('<option/>').text(themes[i]['name']).data('params', themes[i]['params']));
		}
		$(this).change(function()
		{
			var target = $(this).parents('.export');
			var textarea = target.find('textarea');
			var params = $(this).find('option:selected').data('params');
			var merged = '';
			for (key in params)
			{
				merged += '&' + key + '=' + params[key];
			}
			textarea.val(textarea.val().replace(/(type=[0-9]*).*?\[/, '$1' + merged + '['));
			updatePreview(target);
			if ($(this).find('option:selected').text() == 'Custom')
			{
				target.find('select.color').trigger('change'); /*update colorpicker on theme change*/
				target.data('interval', window.setInterval(function()
				{
					updatePreview(target);
				}, 750));
				target.find('.custom-theme').animate({width: 'show'});
			}
			else
			{
				target.find('.custom-theme').animate({width: 'hide'});
				window.clearInterval(target.data('interval'));
			}
		});
	});

	/* select text in textarea on click */
	$('.export textarea').click(function()
	{
		$(this).select();
	});

	function changedColor(color)
	{
		var target = this.target;
		var key = target.parents('.export').find('select.color').val();
		var textarea = target.parents('.export').find('textarea');
		textarea.val(textarea.val().replace(new RegExp(key + '=([a-f0-9]+)'), key + '=' + color.substr(1)));
	}

	/* custom theme editing */
	$('.export .colorpicker').each(function()
	{
		$.farbtastic($(this), changedColor).target = $(this);
	});

	$('.export select.color').change(function()
	{
		var textarea = $(this).parents('.export').find('textarea');
		var colorpicker = $(this).parents('.export').find('.colorpicker');
		var key = $(this).val();
		var matches = textarea.val().match(new RegExp(key + '=([a-f0-9]{2})?([a-f0-9]{6})'));
		$.farbtastic(colorpicker).setColor('#' + matches[2]);
	});

	$('.export .close').click(function(e)
	{
		$('.popup-wrapper').fadeOut('fast');
		e.preventDefault();
	});

});
