<?php
require_once 'src/core.php';

if (count($argv) > 1)
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
		foreach (R::getAll('SELECT um.mal_id, um.media FROM usermedia um LEFT OUTER JOIN media m ON um.mal_id = m.mal_id AND um.media = m.media WHERE um.user_id = ? AND (m.id IS NULL OR m.processed <= DATETIME("now", "-21 days"))', [$context->user->id]) as $row)
		{
			printf('Processing %s #%d' . PHP_EOL, Media::toString($row['media']), $row['mal_id']);
			$mediaProcessors[$row['media']]->process($row['mal_id']);
		}
	}
	catch (Exception $e)
	{
		Logger::log(Config::$errorLogPath, $e);
		echo $e . PHP_EOL;
	}
}
