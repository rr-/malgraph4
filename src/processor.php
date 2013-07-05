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
		#todo:
		#1. download user info
		#2. convert user info to sqlite
		#3. get from sqlite info on missing a/m
		#4. download a/m info
		#5. convert a/m info  to sqlite
		#6. make html from sqlite

		#1. downloading should be done in separate class
		#2. updating should cascade on delete, and then insert back
		#3. creating html should be separate file for each module,
		#but common output should be abstracted in separate file
	}

	public function processGlobals()
	{
		#just create html from sqlite.
		#don't think about downloading or anything like that here
	}
}
