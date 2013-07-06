<?php
require_once 'singleton.php';

class Config extends Singleton
{
	static $userQueuePath;
	static $debugCron;

	protected static function doInit()
	{
		self::$userQueuePath = __DIR__ . '/../data/users.lst';
		self::$debugCron = true;
	}
}

Config::init();
