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
	static $baseUrl;
	static $googleAdsEnabled;
	static $googleAnalyticsEnabled;
	static $banListPath;
	static $errorLogPath;
	static $achievementsDefinitionPath;
	static $achievementsImageDir;
	static $sendReferrer;
	static $maxDbBindings;
	static $usersPerCronRun;

	public static function doInit()
	{
		$rootDir = join(DIRECTORY_SEPARATOR, [__DIR__, '..', 'data', '']);
		self::$userQueuePath = $rootDir . 'users.lst';
		self::$mirrorPath = $rootDir . 'mirror';
		self::$cachePath = $rootDir . 'cache';
		self::$dbPath = $rootDir . 'db.sqlite';
		self::$banListPath = $rootDir . 'banned.lst';
		self::$errorLogPath = $rootDir . 'errors.log';
		self::$achievementsDefinitionPath = $rootDir . 'achievements.json';
		self::$achievementsImageDir = join(DIRECTORY_SEPARATOR, [$rootDir, '..', 'public_html', 'media', 'img', 'ach']);
		self::$mirrorEnabled = false;
		self::$cacheEnabled = true;
		self::$cacheTimeToLive = 24 * 60 * 60;
		self::$baseUrl = 'http://mal.oko.im/';
		self::$googleAdsEnabled = true;
		self::$googleAnalyticsEnabled = true;
		self::$sendReferrer = true;
		self::$maxDbBindings = 50;
		self::$usersPerCronRun = 5;
	}
}

Config::init();
