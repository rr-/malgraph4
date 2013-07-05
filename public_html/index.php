<?php
chdir('..');
require_once('src/config.php');
require_once('src/error.php');
require_once('src/queue.php');

try
{
	if (isset($_GET['user']))
	{
		$queue = new Queue(Config::$userQueuePath);
		$queue->enqueue($_GET['user']);
	}
}
catch (Exception $e)
{
	var_dump($e);
}
