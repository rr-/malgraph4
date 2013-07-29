<?php
class Cache
{
	private static function urlToPath($url)
	{
		$name = md5($url) . sha1($url);
		return Config::$cachePath . DIRECTORY_SEPARATOR . $name;
	}

	public static function load($url)
	{
		$path = self::urlToPath($url);
		$data = file_get_contents($path);
		$pos = strpos($data, "\n\n");
		$headers = unserialize(substr($data, 0, $pos));
		$contents = substr($data, $pos + 2);
		foreach ($headers as $key => $value)
		{
			HttpHeadersHelper::setCurrentHeader($key, $value);
		}
		echo $contents;
	}

	public static function isFresh($url)
	{
		$path = self::urlToPath($url);
		if (!Config::$cacheEnabled)
		{
			return false;
		}
		if (!file_exists($path))
		{
			return false;
		}
		if (time() - filemtime($path) > Config::$cacheTimeToLive)
		{
			return false;
		}
		return true;
	}

	private static $state = 0;
	private static $path = null;
	public static function beginSave($url)
	{
		if (self::$state != 0)
		{
			throw new BadCacheSaveStateException();
		}
		self::$state = 1;
		self::$path = self::urlToPath($url);
		ob_start();
	}

	public static function endSave()
	{
		if (self::$state != 1)
		{
			throw new BadCacheSaveStateException();
		}
		self::$state = 0;

		$headers = HttpHeadersHelper::getCurrentHeaders();
		$contents = ob_get_contents();
		ob_end_clean();

		$handle = fopen(self::$path, 'wb');
		flock($handle, LOCK_EX);
		fwrite($handle, serialize($headers));
		fwrite($handle, "\n\n");
		fwrite($handle, $contents);
		fclose($handle);

		echo $contents;
	}
}
