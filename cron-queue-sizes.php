<?php
require_once 'src/core.php';
CronRunner::run(__FILE__, function($logger)
{
	$limit = 2*24*60/5;
	$queueSizes = TextHelper::loadJson(Config::$userQueueSizesPath, true);
	$queue = new Queue(Config::$userQueuePath);

	$key = date('c');
	$queueSizes[$key] = $queue->size();
	ksort($queueSizes, SORT_NATURAL | SORT_FLAG_CASE);
	while (count($queueSizes) > $limit)
	{
		array_shift($queueSizes);
	}

	TextHelper::putJson(Config::$userQueueSizesPath, $queueSizes);
});
