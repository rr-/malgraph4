<?php
class BenchmarkHelper extends Singleton
{
	private static $start;

	public static function benchmark($message)
	{
		$delta = microtime(true) - self::$start;
		printf('%.05f: %s', $delta, $message);
	}

	public static function doInit()
	{
		self::$start = microtime(true);
	}
}

BenchmarkHelper::init();
