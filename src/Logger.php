<?php
class Logger
{
	private $path;

	public function __construct($name)
	{
		$fileName = basename($name) . '.log';
		$this->path = Config::$logsPath . DIRECTORY_SEPARATOR . $fileName;
	}

	public function purge()
	{
		$handle = fopen($this->path, 'wb');
		fclose($handle);
	}

	public function log($data)
	{
		$data = call_user_func_array('sprintf', func_get_args());
		$header = sprintf('[%s] ', self::getTimestamp());
		$footer = PHP_EOL;
		$handle = fopen($this->path, 'ab');
		flock($handle, LOCK_EX);
		fwrite($handle, $header);
		fwrite($handle, $data);
		fwrite($handle, $footer);
		fclose($handle);

		if (!isset($_SERVER['HTTP_HOST']))
		{
			echo $header;
			echo $data;
			echo $footer;
			flush();
		}
	}

	private static function getTimestamp()
	{
		return date('Y-m-d H:i:s');
	}
}
