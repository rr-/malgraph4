<?php
require_once 'src/core.php';

$userNames = [];
$queue = new Queue(Config::$userQueuePath);
$userNames = $queue->dequeue(Config::$usersPerCronRun);
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
	R::begin();
	try
	{
		printf('Processing user %s' . PHP_EOL, $userName);
		$query = 'SELECT 0 FROM user WHERE LOWER(name) = LOWER(?)' .
			' AND processed >= DATETIME("now", "-1 days")';
		if (R::getAll($query, [$userName]))
		{
			echo 'Too soon' . PHP_EOL;
			R::rollback();
			continue;
		}
		$context = $userProcessor->process($userName);
		R::commit();

		R::begin();
		$done = 0;
		$query = 'SELECT um.mal_id, um.media FROM usermedia um' .
			' LEFT OUTER JOIN media m ON um.mal_id = m.mal_id AND um.media = m.media' .
			' WHERE um.user_id = ?' .
			' AND (m.id IS NULL OR m.processed <= DATETIME("now", "-21 days"))';
		foreach (R::getAll($query, [$context->user->id]) as $row)
		{
			$row = ReflectionHelper::arrayToClass($row);
			printf('Processing %s #%d' . PHP_EOL, Media::toString($row->media), $row->mal_id);
			$mediaProcessors[$row->media]->process($row->mal_id);
			++ $done;
			if ($done % Config::$transactionCommitFrequency == Config::$transactionCommitFrequency - 1)
			{
				R::commit();
				R::begin();
			}
		}
		R::commit();
	}
	catch (BadProcessorKeyException $e)
	{
		R::rollback();
		echo $e->getMessage() . PHP_EOL;
	}
	catch (Exception $e)
	{
		R::rollback();
		Logger::log(Config::$errorLogPath, $e);
		echo $e . PHP_EOL;
	}
}
