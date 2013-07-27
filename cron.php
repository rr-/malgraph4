<?php
require_once 'src/core.php';

$userNames = [];
$queue = new Queue(Config::$userQueuePath);
for ($i = 0; $i < Config::$usersPerCronRun; $i ++)
{
	$userNames []= $queue->dequeue();
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
		$sql = 'SELECT 0 FROM user WHERE LOWER(name) = LOWER(?)' .
			' AND processed >= DATETIME("now", "-1 days")';
		if (R::getAll($sql, [$userName]))
		{
			echo 'Too soon' . PHP_EOL;
			continue;
		}
		$context = $userProcessor->process($userName);
		$sql = 'SELECT um.mal_id, um.media FROM usermedia um' .
			' LEFT OUTER JOIN media m ON um.mal_id = m.mal_id AND um.media = m.media' .
			' WHERE um.user_id = ?' .
			' AND (m.id IS NULL OR m.processed <= DATETIME("now", "-21 days"))';
		foreach (R::getAll($sql, [$context->user->id]) as $row)
		{
			$row = ReflectionHelper::arrayToClass($row);
			printf('Processing %s #%d' . PHP_EOL, Media::toString($row->media), $row->mal_id);
			$mediaProcessors[$row->media]->process($row->mal_id);
		}
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
