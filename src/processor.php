<?php
require_once 'config.php';
require_once 'error.php';
require_once 'queue.php';

class Processor
{
	public function processOne()
	{
		$queue = new Queue(Config::$userQueuePath);
		var_dump($queue->dequeue());
	}
}
