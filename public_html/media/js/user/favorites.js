$(function()
{
	$('.genres, .creators').each(function()
	{
		var section = $(this);
		section.find('.entries-trigger').click(function(e)
		{
			e.preventDefault();
			var target = $(this);
			toggleEntries(section.find('.entries-wrapper'),
				{'sender': target.attr('data-sender'), 'filter-param': target.attr('data-id')},
				true,
				function()
				{
					section.find('.entries-wrapper-row').insertAfter(target.parents('tr'));
				}
			);
		});
	});

	$.tablesorter.addWidget(
	{
		id: 'ord',
		format: function(table)
		{
			for (var i = 0; i < table.tBodies[0].rows.length; i ++)
			{
				$('tbody tr:eq(' + i + ') td.ord', table).text(i + 1);
			}
		}
	});

	var opt = {
		headers:
		{
			0:
			{
				sorter: false
			},
			5:
			{
				sorter: 'percent'
			}
		},

		widgets:
		[
			'ord'
		],

		sortList:
		[
			[4,1]
		]
	};

	$('table').tablesorter(opt);
});

