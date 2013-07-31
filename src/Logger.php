<?php
class Logger
{
	private static function getTimestamp()
	{
		return date('Y-m-d H:i:s');
	}

	private static function getRequestUri()
	{
		return isset($_SERVER['REQUEST_URI'])
			? $_SERVER['REQUEST_URI']
			: 'cron';
	}

	public static function purge($logPath)
	{
		$handle = fopen($logPath, 'wb');
		fclose($handle);
	}

	public static function logLine($logPath, $line = null)
	{
		$handle = fopen($logPath, 'ab');
		flock($handle, LOCK_EX);
		$data = sprintf('%s %s %s' . PHP_EOL,
			self::getTimestamp(),
			self::getRequestUri(),
			$line !== null ? $line : '---');
		fwrite($handle, $data);
		fclose($handle);
	}

	public static function log($logPath, $data)
	{
		$handle = fopen($logPath, 'ab');
		flock($handle, LOCK_EX);
		$header = sprintf('--- %s %s ---' . PHP_EOL,
			self::getTimestamp(),
			self::getRequestUri());
		fwrite($handle, $header);
		fwrite($handle, $data);
		fwrite($handle, str_repeat(PHP_EOL, 3));
		fclose($handle);
	}
}
