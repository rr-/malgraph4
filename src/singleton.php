<?php
abstract class Singleton
{
	private static $initialized = false;

	public static function init()
	{
		if (self::$initialized)
		{
			return;
		}
		self::$initialized = true;
		static::doInit();
	}

	protected static abstract function doInit();

	private function __construct()
	{
	}
}
