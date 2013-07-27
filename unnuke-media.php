<?php
require_once 'src/core.php';

$mediaProcessors =
[
	Media::Anime => new AnimeProcessor(),
	Media::Manga => new MangaProcessor()
];

$sql = 'SELECT um.mal_id, um.media FROM usermedia um' .
	' LEFT JOIN media m ON um.media = m.media AND um.mal_id = m.mal_id' .
	' WHERE m.id IS NULL' .
	' GROUP BY um.media || um.mal_id';

$rows = R::getAll($sql);
foreach ($rows as $row)
{
	$row = ReflectionHelper::arrayToClass($row);
	printf('Processing %s #%d' . PHP_EOL, Media::toString($row->media), $row->mal_id);
	try
	{
		$mediaProcessors[$row->media]->process($row->mal_id);
	}
	catch (BadProcessorKeyException $e)
	{
		echo $e->getMessage() . PHP_EOL;
	}
	catch (Exception $e)
	{
		Logger::log(Config::$errorLogPath, $e);
		echo $e . PHP_EOL;
		$exitCode = 1;
	}
}
