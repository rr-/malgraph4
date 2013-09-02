<?php
class Config extends Singleton
{
	static $usersPerCronRun;
	static $userQueuePath;
	static $mirrorPath;
	static $mirrorEnabled;
	static $cachePath;
	static $cacheEnabled;
	static $cacheTimeToLive;
	static $dbPath;
	static $maxDbBindings;
	static $transactionCommitFrequency;
	static $bannedUsersListPath;
	static $bannedGenresListPath;
	static $bannedCreatorsListPath;
	static $bannedGenresForRecsListPath;
	static $bannedFranchiseCouplingListPath;
	static $achievementsDefinitionPath;
	static $staticRecommendationListPath;
	static $errorLogPath;
	static $globalsCachePath;
	static $userQueueSizesPath;
	static $mediaDirectory;
	static $mediaUrl;
	static $baseUrl;
	static $googleAdsEnabled;
	static $googleAnalyticsEnabled;
	static $adminPassword;
	static $maintenanceMessage;
	static $sendReferrer;
	static $enforcedDomain;
	static $version;

	public static function doInit()
	{
		$dataRootDir = join(DIRECTORY_SEPARATOR, [__DIR__, '..', 'data', '']);
		$htmlRootDir = join(DIRECTORY_SEPARATOR, [__DIR__, '..', 'public_html']);

		self::$usersPerCronRun = 5;
		self::$userQueuePath = $dataRootDir . 'users.lst';

		self::$mirrorEnabled = false;
		self::$mirrorPath = $dataRootDir . 'mirror';
		self::$cacheEnabled = true;
		self::$cachePath = $dataRootDir . 'cache';
		self::$cacheTimeToLive = 24 * 60 * 60;

		self::$dbPath = $dataRootDir . 'db.sqlite';
		self::$transactionCommitFrequency = 20;
		self::$maxDbBindings = 50;

		self::$bannedUsersListPath = $dataRootDir . 'banned-users.lst';
		self::$bannedGenresListPath = $dataRootDir . 'banned-genres.lst';
		self::$bannedCreatorsListPath = $dataRootDir . 'banned-creators.lst';
		self::$bannedGenresForRecsListPath = $dataRootDir . 'recs-banned-genres.lst';
		self::$bannedFranchiseCouplingListPath = $dataRootDir . 'banned-franchise-coupling.lst';
		self::$achievementsDefinitionPath = $dataRootDir . 'achievements.json';
		self::$staticRecommendationListPath = $dataRootDir . 'static-recommendations.lst';

		self::$errorLogPath = $dataRootDir . 'errors.log';
		self::$globalsCachePath = $dataRootDir . 'globals-cache.json';
		self::$userQueueSizesPath = $dataRootDir . 'queue-sizes.json';

		self::$mediaDirectory = $htmlRootDir . DIRECTORY_SEPARATOR . 'media';
		self::$mediaUrl = '/media/';
		self::$baseUrl = isset($_SERVER['HTTP_HOST'])
			? 'http://' . $_SERVER['HTTP_HOST'] . '/'
			: 'http://mal.oko.im/';

		self::$googleAdsEnabled = true;
		self::$googleAnalyticsEnabled = true;
		self::$adminPassword = 'supersaiyan';
		self::$maintenanceMessage = null;
		self::$sendReferrer = true;
		self::$enforcedDomain = null;

		self::$version = '4.0.5';
	}
}

Config::init();
