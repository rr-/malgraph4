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

$userProcessor = new UserProcessor();
$queue = new Queue(Config::$userQueuePath);

$processed = 0;
while ($processed < Config::$usersPerCronRun)
{
	$userName = $queue->dequeue();
	if ($userName === null)
	{
		exit(0);
	}

	try
	{
		printf('Processing user %s' . PHP_EOL, $userName);
		$query = 'SELECT 0 FROM user WHERE LOWER(name) = LOWER(?)' .
			' AND processed >= DATETIME("now", "-1 days")';
		if (R::getAll($query, [$userName]))
		{
			echo 'Too soon' . PHP_EOL;
			continue;
		}
		++ $processed;
		$userProcessor->process($userName);
	}
	catch (BadProcessorKeyException $e)
	{
		echo $e->getMessage() . PHP_EOL;
	}
	catch (Exception $e)
	{
		$queue->enqueue($userName);
		Logger::log(Config::$errorLogPath, $e);
		echo $e . PHP_EOL;
	}
}
