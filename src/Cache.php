<?php
class Cache
{
	public static function load($url)
	{
	}

	public static function isFresh($url)
	{
		return false;
	}

	private static $state = 0;
	public static function beginSave($url)
	{
		if (self::$state != 0)
		{
			throw new BadCacheSaveStateException();
		}
		self::$state = 1;
	}

	public static function endSave()
	{
		if (self::$state != 1)
		{
			throw new BadCacheSaveStateException();
		}
		self::$state = 0;
	}
}
