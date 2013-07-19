<?php
require_once 'src/core.php';

if (isset($argv))
{
	$userNames = array_slice($argv, 1);
}
else
{
	$userNames = [];
	$queue = new Queue(Config::$userQueuePath);
	if (Config::$debugCron)
	{
		$userNames []= $queue->peek();
	}
	else
	{
		for ($i = 0; $i < 5; $i ++)
		{
			$userNames []= $queue->dequeue();
		}
	}
}
$userNames = array_filter($userNames);
if (empty($userNames))
{
	exit(0);
}

$userProcessor = new UserProcessor();
$mediaProcessors =
[
	Media::Anime => new AnimeProcessor(),
	Media::Manga => new MangaProcessor()
];

foreach ($userNames as $userName)
{
	try
	{
		printf('Processing user %s' . PHP_EOL, $userName);
		$context = $userProcessor->process($userName);
		foreach (R::findAll('usermedia', 'user_id = ?', [$context->userId]) as $userMedia)
		{
			if (!R::findOne('media', 'mal_id = ? AND media = ? AND processed >= DATETIME("now", "-21 days")', [$userMedia->mal_id, $userMedia->media]))
			{
				printf('Processing %s #%d' . PHP_EOL, Media::toString($userMedia->media), $userMedia->mal_id);
				$mediaProcessors[$userMedia->media]->process($userMedia->mal_id);
			}
		}
	}
	catch (Exception $e)
	{
		Logger::log(Config::$errorLogPath, $e);
		echo $e . PHP_EOL;
	}
}
