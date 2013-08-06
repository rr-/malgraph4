<?php
require_once 'src/core.php';

$mediaProcessors =
[
	Media::Anime => new AnimeProcessor(),
	Media::Manga => new MangaProcessor()
];

$query = 'SELECT um.mal_id, um.media FROM usermedia um' .
	' GROUP BY um.media, um.mal_id' .
	' HAVING NOT EXISTS(' .
		'SELECT null FROM media m' .
		' WHERE m.mal_id = um.mal_id AND m.media = um.media' .
	') ORDER BY um.mal_id';

$exitCode = 0;
$rows = R::getAll($query);
$rows = ReflectionHelper::arraysToClasses($rows);
$attempts = 0;
while (!empty($rows))
{
	$row = reset($rows);
	printf('Processing %s #%d' . PHP_EOL, Media::toString($row->media), $row->mal_id);
	try
	{
		$mediaProcessors[$row->media]->process($row->mal_id);
		array_shift($rows);
		$attempts = 0;
	}
	catch (Exception $e)
	{
		echo $e->getMessage() . PHP_EOL;
		$exitCode = 1;
		$attempts ++;
		if ($attempts >= 3)
		{
			array_shift($rows);
			$attempts = 0;
		}
		else
		{
			sleep(1);
		}
	}
}
exit($exitCode);
