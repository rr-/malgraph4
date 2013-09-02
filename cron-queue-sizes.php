<?php
require_once 'src/core.php';
try
{
	SingleInstance::run(__FILE__);
}
catch (Exception $e)
{
	echo $e->getMessage() . PHP_EOL;
	exit(1);
}

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
