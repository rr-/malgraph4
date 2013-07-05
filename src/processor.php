<?php
require_once 'config.php';
require_once 'queue.php';
require_once 'downloader.php';

class Processor
{
	public function processOne()
	{
		$queue = new Queue(Config::$userQueuePath);
		$userName = Config::$debugCron
			? $queue->peek()
			: $queue->dequeue();
		if (empty($userName))
		{
			return;
		}

		$urls =
		[
			'http://myanimelist.net/profile/' . $userName,
			#'http://myanimelist.net/animelist/' . $userName,
			#'http://myanimelist.net/mangalist/' . $userName,
			'http://myanimelist.net/malappinfo.php?u=' . $userName . '&status=all&type=anime',
			'http://myanimelist.net/malappinfo.php?u=' . $userName . '&status=all&type=manga',
		];
		print_r($urls);
		$downloader = new Downloader();
		$results = $downloader->downloadMulti($urls);
		var_dump($results);

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
