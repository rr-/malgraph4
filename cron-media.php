<?php
require_once 'src/core.php';
try
{
	SingleInstance::run();
}
catch (Exception $e)
{
	echo $e->getMessage() . PHP_EOL;
	exit(1);
}

$mediaProcessors =
[
	Media::Anime => new AnimeProcessor(),
	Media::Manga => new MangaProcessor()
];

$query = 'CREATE TEMPORARY TABLE hurr (media VARCHAR(1), mal_id INTEGER)';
R::exec($query);

$query = 'INSERT INTO hurr SELECT DISTINCT um.media, um.mal_id FROM usermedia um';
R::exec($query);

$query = 'SELECT um.media, um.mal_id' .
	' FROM hurr um' .
	' LEFT OUTER JOIN media m ON m.media = um.media AND m.mal_id = um.mal_id' .
	' WHERE m.id IS NULL' .
	' OR (m.processed < DATETIME("now", "-21 days") AND m.publishing_status != ?)';
$rows = R::getAll($query, [MediaStatus::Finished]);
$rows = ReflectionHelper::arraysToClasses($rows);

foreach ($rows as $row)
{
	try
	{
		printf('Processing %s #%d' . PHP_EOL, Media::toString($row->media), $row->mal_id);
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
	}
}
