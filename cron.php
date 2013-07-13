<?php
require_once 'src/core.php';

try
{
	$queue = new Queue(Config::$userQueuePath);
	$userName = Config::$debugCron
		? $queue->peek()
		: $queue->dequeue();

	$processor = new UserProcessor();
	$processor->process($userName);

	$processor = new AnimeProcessor();
	$processor->process(1);

	$processor = new MangaProcessor();
	$processor->process(3);
}
catch (Exception $e)
{
	#todo:
	#better error handling
	echo $e . PHP_EOL;
}
