<?php
class Config
{
	static $userQueuePath;

	private function __construct()
	{
	}
}
Config::$userQueuePath = __DIR__ . '/../data/users.lst';
