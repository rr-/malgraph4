<?php
require_once __DIR__ . '/../src/core.php';

$achList = UserControllerAchievementsModule::getAchievementsDefinitions();

$titles =
	[
		Media::Anime =>
		[
			'Mean score' =>
			[
				'High mean score',
				'Low mean score',
				'Very low mean score'
			]
		],
		Media::Manga =>
		[
			'Mean score' =>
			[
				'High mean score',
				'Low mean score',
				'Very low mean score'
			]
		]
	];

foreach (Media::getConstList() as $media)
{
	foreach ($titles[$media] as $title => $titlesToMerge)
	{
		$itemsToMerge = [];
		$keysToMerge = [];
		foreach ($titlesToMerge as $titleToMerge)
		{
			foreach ($achList[$media] as $key => $definition)
			{
				if ($definition->{'wiki-title'} == $titleToMerge)
				{
					$itemsToMerge []= $definition;
					$keysToMerge []= $key;
				}
			}
		}

		$finalItem = array_shift($itemsToMerge);
		$finalItem->{'wiki-title'} = $title;
		foreach ($itemsToMerge as $itemToMerge)
		{
			$finalItem->achievements = array_merge(
				$finalItem->achievements,
				$itemToMerge->achievements);
		}

		array_shift($keysToMerge);
		foreach ($keysToMerge as $key)
			unset($achList[$media][$key]);
	}
}

echo <<<EOF
All genre-based achievements have four levels. Max level images for these are
similar to each other - black background, white glow, and a simple symbol at
the center. Count-based badges have 12 levels, and score-based have three
pseudo-levels (anime only for now). There are some other special badges awarded
for achieving something big. In total, we have over 80 images that make you want
to read more manga or watch more anime. Or so we think.

If you&rsquo;re curious about titles that count towards certain badges, read
[these files](https://github.com/rr-/malgraph4/tree/master/data/achievements),
but note that some genre-based achievements have their title list generated
automatically from genres on MAL. These can also have additional titles added
manually, to fill up genre holes left by MAL (e.g. sequel to a historical anime
isn&rsquo;t tagged as such).

Thresholds for (most) anime genres are 25 titles for level one, 40 for lvl2, 60
for lvl3 and 100 for the maximum level. Manga thresholds (and
anime-mahoushoujo) are 15, 30, 50 and 80. The romance badges have increased
thresholds, since this genre tag is ubiquitous.

## Table of contents
EOF;
printf(PHP_EOL);

foreach (Media::getConstList() as $media)
{
	printf('### %s  ' . PHP_EOL, ucfirst(Media::toString($media)));
	foreach ($achList[$media] as $groupData)
	{
		printf('* [%s / %s](#%s)' . PHP_EOL,
			ucfirst(Media::toString($media)),
			$groupData->{'wiki-title'},
			md5($media . $groupData->{'wiki-title'}));
	}
	printf(PHP_EOL);
}
printf(PHP_EOL);

foreach (Media::getConstList() as $media)
{
	printf('## %s achievements' . PHP_EOL, ucfirst(Media::toString($media)));

	foreach ($achList[$media] as $groupData)
	{
		printf('<div id="%s"></div>' . PHP_EOL,
			md5($media . $groupData->{'wiki-title'}));

		printf('### %s / %s  ' . PHP_EOL,
			ucfirst(Media::toString($media)),
			$groupData->{'wiki-title'});

		if (isset($groupData->{'wiki-desc'}))
		{
			printf($groupData->{'wiki-desc'});
		}
		printf(PHP_EOL);
		printf(PHP_EOL);

		printf('<table>');
		foreach ($groupData->achievements as $achievement)
		{
			printf('<tr><td>');
			if (isset($achievement->path))
			{
				$url = '/media/img/ach/' . $achievement->path;
				printf('![%s](%s)' . PHP_EOL,
					$achievement->id,
					UrlHelper::absoluteUrl($url));
			}
			printf('</td><td>');
			printf('**%s**  ' . PHP_EOL, $achievement->title);
			printf('%s' . PHP_EOL, $achievement->desc);
			printf('</td></tr>' . PHP_EOL);
		}
		printf('</table>' . '&nbsp;'/*markdown bug*/ . PHP_EOL);
	}
	printf(PHP_EOL);
}
