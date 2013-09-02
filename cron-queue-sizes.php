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

$queueSizes = TextHelper::loadJson(Config::$userQueueSizesPath);
$queue = new Queue(Config::$userQueuePath);
$key = date('Y-m-d H:i');
$queueSizes[$key] = $queue->size();
$limit = 2*24*60;
while (count($queueSizes) > $limit)
{
	array_shift($queueSizes);
}
TextHelper::putJson(Config::$userQueueSizesPath, $queueSizes);
