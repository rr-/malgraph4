<?php
abstract class Singleton
{
	private static function isInitialized()
	{
		static $initialized = false;
		$ret = $initialized;
		$initialized = true;
		return $ret;
	}

	public static function init()
	{
		if (!static::isInitialized())
		{
			static::doInit();
		}
	}

	protected static abstract function doInit();

	private function __construct()
	{
	}
}
