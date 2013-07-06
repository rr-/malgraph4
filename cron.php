<?php
require_once 'src/user_processor.php';
require_once 'src/config.php';
require_once 'src/queue.php';
require_once 'src/error.php';

try
{
	$queue = new Queue(Config::$userQueuePath);
	$userName = Config::$debugCron
		? $queue->peek()
		: $queue->dequeue();

	$processor = new UserProcessor();
	$processor->process($userName);
}
catch (Exception $e)
{
	#todo:
	#better error handling
	var_dump($e);
}
