<?php
class Config extends Singleton
{
	static $userQueuePath;
	static $mirrorPath;
	static $mirrorEnabled;
	static $cachePath;
	static $cacheEnabled;
	static $cacheTimeToLive;
	static $dbPath;
	static $debugCron;

	public static function doInit()
	{
		self::$userQueuePath = __DIR__ . '/../data/users.lst';
		self::$mirrorPath = __DIR__ . '/../data/mirror/';
		self::$cachePath = __DIR__ . '/../data/cache/';
		self::$dbPath = __DIR__ . '/../data/db.sqlite';
		self::$debugCron = true;
		self::$mirrorEnabled = true;
		self::$cacheEnabled = true;
		self::$cacheTimeToLive = 24 * 60 * 60;
	}
}

Config::init();
