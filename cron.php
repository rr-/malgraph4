<?php
require_once 'src/core.php';

try
{
	$queue = new Queue(Config::$userQueuePath);
	$userName = Config::$debugCron
		? $queue->peek()
		: $queue->dequeue();
	if (empty($userName))
	{
		return;
	}

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
	$confirmStmt = $pdo->prepare('SELECT mal_id FROM media WHERE mal_id = ? AND media = ? AND processed >= DATETIME("now", "-21 days")');
	foreach ($stmt->fetchAll() as $row)
	{
		$confirmStmt->execute([$row->mal_id, $row->media]);
		$doProcess = empty($confirmStmt->fetch());
		if ($doProcess)
		{
			echo sprintf('Processing %s #%d' . PHP_EOL, Media::toString($row->media), $row->mal_id);
			$mediaProcessors[$row->media]->process($row->mal_id);
		}
	}
}
catch (Exception $e)
{
	Logger::log(Config::$errorLogPath, $e);
	echo $e . PHP_EOL;
}
