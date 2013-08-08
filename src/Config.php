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
	static $bannedUsersListPath;
	static $bannedGenresListPath;
	static $bannedCreatorsListPath;
	static $bannedGenresForRecsListPath;
	static $bannedFranchiseCouplingListPath;
	static $staticRecommendationListPath;
	static $errorLogPath;
	static $achievementsDefinitionPath;
	static $sendReferrer;
	static $maxDbBindings;
	static $usersPerCronRun;
	static $mediaDirectory;
	static $mediaUrl;
	static $globalsCachePath;
	static $transactionCommitFrequency;
	static $adminPassword;

	public static function doInit()
	{
		$rootDir = join(DIRECTORY_SEPARATOR, [__DIR__, '..', 'data', '']);
		self::$userQueuePath = $rootDir . 'users.lst';
		self::$mirrorPath = $rootDir . 'mirror';
		self::$cachePath = $rootDir . 'cache';
		self::$dbPath = $rootDir . 'db.sqlite';
		self::$bannedUsersListPath = $rootDir . 'banned-users.lst';
		self::$bannedGenresListPath = $rootDir . 'banned-genres.lst';
		self::$bannedCreatorsListPath = $rootDir . 'banned-creators.lst';
		self::$bannedGenresForRecsListPath = $rootDir . 'recs-banned-genres.lst';
		self::$bannedFranchiseCouplingListPath = $rootDir . 'banned-franchise-coupling.lst';
		self::$errorLogPath = $rootDir . 'errors.log';
		self::$achievementsDefinitionPath = $rootDir . 'achievements.json';
		self::$staticRecommendationListPath = $rootDir . 'static-recommendations.lst';
		self::$globalsCachePath = $rootDir . 'globals-cache.dat';
		self::$mediaDirectory = join(DIRECTORY_SEPARATOR, [$rootDir, '..', 'public_html', 'media']);
		self::$mediaUrl = '/media/';
		self::$mirrorEnabled = false;
		self::$cacheEnabled = true;
		self::$cacheTimeToLive = 24 * 60 * 60;
		self::$baseUrl = 'http://mal.oko.im/';
		self::$googleAdsEnabled = true;
		self::$googleAnalyticsEnabled = true;
		self::$sendReferrer = true;
		self::$maxDbBindings = 50;
		self::$usersPerCronRun = 5;
		self::$transactionCommitFrequency = 20;
		self::$adminPassword = 'supersaiyan';
	}
}

Config::init();
