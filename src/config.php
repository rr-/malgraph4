<?php
require_once 'singleton.php';

class Config extends Singleton
{
	static $userQueuePath;
	static $mirrorPath;
	static $mirrorEnabled;
	static $debugCron;

	protected static function doInit()
	{
		self::$userQueuePath = __DIR__ . '/../data/users.lst';
		self::$mirrorPath = __DIR__ . '/../data/mirror/';
		self::$debugCron = true;
		self::$mirrorEnabled = true;
	}
}

Config::init();
