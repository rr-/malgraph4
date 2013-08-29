<?php
class SingleInstance
{
	private static $fileHandle = null;

	public static function run()
	{
		$fileName = $_SERVER['SCRIPT_FILENAME'] . '.lock';
		$lockFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $fileName;
		self::$fileHandle = fopen($lockFile, 'wb');
		if (!flock(self::$fileHandle, LOCK_EX | LOCK_NB))
		{
			throw new InstanceAlreadyRunningException();
		}
	}

	public static function destruct()
	{
		if (self::$fileHandle != null)
		{
			fclose(self::$fileHandle);
		}
	}
}

register_shutdown_function(['SingleInstance', 'destruct']);
