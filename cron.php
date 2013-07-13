<?php
require_once 'src/core.php';

try
{
	$queue = new Queue(Config::$userQueuePath);
	$userName = Config::$debugCron
		? $queue->peek()
		: $queue->dequeue();

	$processor = new UserProcessor();
	$context = $processor->process($userName);

	$mediaProcessors =
	[
		Media::Anime => new AnimeProcessor(),
		Media::Manga => new MangaProcessor()
	];

	$pdo = Database::getPDO();
	$stmt = $pdo->prepare('SELECT mal_id, media FROM user_media_list WHERE user_id = ?');
	$stmt->execute([$context->userId]);
	foreach ($stmt->fetchAll() as $row)
	{
		echo sprintf('Processing %s #%d' . PHP_EOL, Media::toString($row->media), $row->mal_id);
		$mediaProcessors[$row->media]->process($row->mal_id);
	}
}
catch (Exception $e)
{
	#todo:
	#better error handling
	echo $e . PHP_EOL;
}
