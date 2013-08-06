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

$num = 20;
$done = 0;
R::begin();

while (!empty($rows))
{
	$row = reset($rows);
	printf('Processing %s #%d, %d left' . PHP_EOL, Media::toString($row->media), $row->mal_id, count($rows));
	try
	{
		$mediaProcessors[$row->media]->process($row->mal_id);
		array_shift($rows);
		++ $done;
	}
	catch (Exception $e)
	{
		R::rollback();
		R::begin();
		echo $e->getMessage() . PHP_EOL;
		$exitCode = 1;
		array_shift($rows);
	}

	if ($done % Config::$transactionCommitFrequency == Config::$transactionCommitFrequency - 1)
	{
		R::commit();
		R::begin();
	}
}

R::commit();
exit($exitCode);
