<?php
require_once 'src/core.php';
CronRunner::run(__FILE__, function($logger)
{
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
			$logger->log('Processing %s #%d', Media::toString($row->media), $row->mal_id);
			$mediaProcessors[$row->media]->process($row->mal_id);
		}
		catch (BadProcessorKeyException $e)
		{
			$logger->log($e->getMessage());
		}
		catch (Exception $e)
		{
			$logger->log($e);
		}
	}
});
