<?php
require_once 'downloader.php';
require_once 'processor.php';

class UserProcessor implements Processor
{
	const URL_PROFILE = 1;
	const URL_ANIMELIST = 2;
	const URL_MANGALIST = 3;
	const URL_ANIMEINFO = 4;
	const URL_MANGAINFO = 5;
	const URL_HISTORY = 6;
	const URL_FRIENDS = 7;
	const URL_CLUBS = 8;

	public function process($userName)
	{
		if (empty($userName))
		{
			return;
		}

		$urls =
		[
			self::URL_PROFILE => 'http://myanimelist.net/profile/' . $userName,
			self::URL_ANIMELIST => 'http://myanimelist.net/animelist/' . $userName,
			self::URL_MANGALIST => 'http://myanimelist.net/mangalist/' . $userName,
			self::URL_ANIMEINFO => 'http://myanimelist.net/malappinfo.php?u=' . $userName . '&status=all&type=anime',
			self::URL_MANGAINFO => 'http://myanimelist.net/malappinfo.php?u=' . $userName . '&status=all&type=manga',
			self::URL_HISTORY => 'http://myanimelist.net/history/' . $userName,
			self::URL_CLUBS => 'http://myanimelist.net/profile/' . $userName . '/clubs',
			self::URL_FRIENDS => 'http://myanimelist.net/profile/' . $userName . '/friends',
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
