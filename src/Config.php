<?php
class Config extends Singleton
{
	static $cronInterval;
	static $usersPerCronRun;
	static $userQueuePath;
	static $userQueueMinWait;
	static $userQueueMaxAttempts;
	static $mediaPerCronRun;
	static $mediaQueuePath;
	static $mediaQueueMinWait;
	static $mediaQueueMaxAttempts;
	static $downloaderUserAgent;
	static $downloaderProxy;
	static $downloaderCookieFilePath;
	static $downloaderMaxParallelJobs;
	static $downloaderMaxTimeout;
	static $downloaderUseMultiHandles;
	static $mirrorPath;
	static $mirrorPurgeFailures;
	static $mirrorEnabled;
	static $cachePath;
	static $cacheEnabled;
	static $cacheTimeToLive;
	static $dbPath;
	static $dbCount;
	static $maxDbBindings;
	static $maxProcessingAttempts;
	static $transactionCommitFrequency;
	static $bannedUsersListPath;
	static $bannedGenresListPath;
	static $bannedCreatorsListPath;
	static $bannedGenresForRecsListPath;
	static $bannedFranchiseCouplingListPath;
	static $staticRecommendationListPath;
	static $achievementsDefinitionsDirectory;
	static $maxLogSize;
	static $logsPath;
	static $keepOldLogs;
	static $globalsCachePath;
	static $userQueueSizesPath;
	static $mediaDirectory;
	static $mediaUrl;
	static $baseUrl;
	static $googleAnalyticsEnabled;
	static $adminPassword;
	static $maintenanceMessage;
	static $noticeMessage;
	static $sendReferrer;
	static $enforcedDomain;
	static $version;
	static $mail;

	public static function doInit()
	{
		$dataRootDir = join(DIRECTORY_SEPARATOR, [__DIR__, '..', 'data', '']);
		$htmlRootDir = join(DIRECTORY_SEPARATOR, [__DIR__, '..', 'public_html']);

		self::$version = '4.2.0';
		self::$mail = 'change_me@example.com';

		self::$cronInterval = 5; //minutes
		self::$usersPerCronRun = 5;
		self::$userQueuePath = $dataRootDir . 'queue-users.lst';
		self::$userQueueMinWait = 24 * 60 * 60; //seconds
		self::$userQueueMaxAttempts = 20;
		self::$mediaPerCronRun = 5;
		self::$mediaQueuePath = $dataRootDir . 'queue-media.lst';
		self::$mediaQueueMinWait = 7 * 24 * 60 * 60; //seconds
		self::$mediaQueueMaxAttempts = 2;

		self::$downloaderUserAgent = 'MALgraph v' . self::$version;
		self::$downloaderProxy = null;
		self::$downloaderCookieFilePath = $dataRootDir . 'cookies.dat';
		self::$downloaderMaxParallelJobs = 10;
		self::$downloaderMaxTimeout = 10000;
		self::$downloaderUseMultiHandles = true;

		self::$mirrorEnabled = false;
		self::$mirrorPath = $dataRootDir . 'mirror';
		self::$mirrorPurgeFailures = true;
		self::$cacheEnabled = true;
		self::$cachePath = $dataRootDir . 'cache';
		self::$cacheTimeToLive = 24 * 60 * 60;

		self::$dbPath = $dataRootDir . 'db';
		self::$dbCount = 64;
		self::$transactionCommitFrequency = 20;
		self::$maxDbBindings = 50;
		self::$maxProcessingAttempts = 1;

		self::$bannedUsersListPath = $dataRootDir . 'banned-users.lst';
		self::$bannedGenresListPath = $dataRootDir . 'banned-genres.lst';
		self::$bannedCreatorsListPath = $dataRootDir . 'banned-creators.lst';
		self::$bannedGenresForRecsListPath = $dataRootDir . 'recs-banned-genres.lst';
		self::$bannedFranchiseCouplingListPath = $dataRootDir . 'banned-franchise-coupling.lst';
		self::$staticRecommendationListPath = $dataRootDir . 'static-recommendations.lst';
		self::$achievementsDefinitionsDirectory = $dataRootDir . 'achievements';

		self::$maxLogSize = 1024 * 1024 * 12;
		self::$keepOldLogs = false;
		self::$logsPath = $dataRootDir . 'logs';
		self::$globalsCachePath = $dataRootDir . 'globals-cache.json';
		self::$userQueueSizesPath = $dataRootDir . 'queue-sizes.json';

		self::$mediaDirectory = $htmlRootDir . DIRECTORY_SEPARATOR . 'media';
		self::$mediaUrl = '/media/';
		self::$baseUrl = isset($_SERVER['HTTP_HOST'])
			? 'http://' . $_SERVER['HTTP_HOST'] . '/'
			: 'http://mal.oko.im/';

		self::$googleAnalyticsEnabled = true;
		self::$adminPassword = 'supersaiyan';
		self::$maintenanceMessage = null;
		self::$noticeMessage = null;
		self::$sendReferrer = true;
		self::$enforcedDomain = null;
	}
}

Config::init();
