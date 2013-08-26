<?php
require_once 'src/core.php';
$achList = TextHelper::loadJson(Config::$achievementsDefinitionPath, true);
$imgFiles = scandir(Config::$mediaDirectory . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'ach');

$keys =
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
		]
	];

foreach (Media::getConstList() as $media)
{
	foreach ($keys[$media] as $group => $keysToMerge)
	{
		$itemsToMerge = [];
		foreach ($keysToMerge as $keyToMerge)
		{
			$itemsToMerge []= $achList[Media::toString($media)][$keyToMerge];
			unset($achList[Media::toString($media)][$keyToMerge]);
		}

		$finalItem = reset($itemsToMerge);
		$finalItem['achievements'] = [];
		foreach ($itemsToMerge as $itemToMerge)
		{
			$finalItem['achievements'] = array_merge($finalItem['achievements'], $itemToMerge['achievements']);
		}
		$achList[Media::toString($media)][$group] = $finalItem;
	}
}
?>
All genre-based achievements have four levels. Max level images for these are
similar to each other - black background, white glow, and a simple symbol at
the center. Count-based badges have 12 levels, and score-based have three
pseudo-levels (anime only for now). There are some other special badges awarded
for achieving something big. In total, we have 71 images that make you want to
read more manga or watch more anime. Or so we think.

If you&rsquo;re curious about titles that count towards certain badges, read
[this file](https://github.com/rr-/malgraph4/blob/master/data/achievements.json),
but note that some genre-based achievements have their title list generated
automatically from genres on MAL. These can also have additional titles added
manually, to fill up genre holes left by MAL (e.g. sequel to a historical anime
isn&rsquo;t tagged as such).

Thresholds for (most) anime genres are 25 titles for level one, 40 for
lvl2, 60 for lvl3 and 100 for the maximum level. Manga thresholds (and
anime-mahoushoujo) are 15, 30, 50 and 80.

<?php foreach (Media::getConstList() as $media): ?>
---
# <?php echo ucfirst(Media::toString($media)) ?> achievements

<?php foreach ($achList[Media::toString($media)] as $groupName => $groupData): ?>
## <?php echo $groupName ?>  
<?php if (isset($groupData['wiki-desc'])): ?>
<?php echo $groupData['wiki-desc'] ?>
<?php endif ?>

<?php foreach ($groupData['achievements'] as $achievement): ?>
<?php $path = null ?>
<?php foreach ($imgFiles as $f): ?>
<?php if (preg_match('/' . $achievement['id'] . '[^0-9a-zA-Z_-]/', $f)): ?>
<?php $path = $f; ?>
<?php endif ?>
<?php endforeach ?>

1. **<?php echo $achievement['title'] ?>**  
![<?php echo $achievement['id'] ?>](<?php echo UrlHelper::absoluteUrl('/media/img/ach/' . $path) ?>)  
<?php echo $achievement['desc'] ?>

<?php endforeach ?>


<?php endforeach ?>
<?php endforeach ?>
