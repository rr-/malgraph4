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
		#todo:
		#try to read cached html
		#otherwise display info about downloading in progress
		#remember this is going to be be a/m and module-wise
	}
	#todo:
	#here go all static pages, like: index, globals, ...
}
catch (Exception $e)
{
	#todo:
	#better error handler
	var_dump($e);
}
