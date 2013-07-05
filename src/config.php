<?php
class Config
{
	static $userQueuePath;
	static $debugCron;

	private function __construct()
	{
	}
}
Config::$userQueuePath = __DIR__ . '/../data/users.lst';
Config::$debugCron = true;
