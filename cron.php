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

	foreach (R::findAll('usermedia', 'user_id = ?', [$context->userId]) as $userMedia)
	{
		if (!R::findOne('media', 'mal_id = ? AND media = ? AND processed >= DATETIME("now", "-21 days")', [$userMedia->mal_id, $userMedia->media]))
		{
			echo sprintf('Processing %s #%d' . PHP_EOL, Media::toString($userMedia->media), $userMedia->mal_id);
			$mediaProcessors[$userMedia->media]->process($userMedia->mal_id);
		}
	}
}
catch (Exception $e)
{
	Logger::log(Config::$errorLogPath, $e);
	echo $e . PHP_EOL;
}
