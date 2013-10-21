<?php
class Logger
{
	private $path;
	private $baseName;
	private $fragmentOpen = false;
	private $handle = null;

	public function __construct($name)
	{
		$fileName = basename($name) . '.log';
		$this->baseName = $name;
		$this->path = Config::$logsPath . DIRECTORY_SEPARATOR . $fileName;
	}

	public function __destruct()
	{
		$this->closefile();
		$this->rotateIfNeeded();
	}

	private function openFile()
	{
		if ($this->handle === null)
		{
			$this->handle = fopen($this->path, 'ab');
			flock($this->handle, LOCK_EX);
		}
	}

	private function write($string)
	{
		assert($this->handle !== null);
		fwrite($this->handle, $string);
		$this->fragmentOpen = true;

		if (!isset($_SERVER['HTTP_HOST']))
		{
			echo $string;
			flush();
		}
	}

	private function closeFile()
	{
		$this->fragmentOpen = false;
		if ($this->handle !== null)
		{
			fclose($this->handle);
			$this->handle = null;
		}
	}

	private function decorate($data)
	{
		if ($this->fragmentOpen)
		{
			return $data;
		}
		return sprintf('[%s|%04x] %s', date('Y-m-d H:i:s'), getmypid(), $data);
	}



	public function purge()
	{
		$handle = fopen($this->path, 'wb');
		fclose($handle);
	}

	public function logFragment($data)
	{
		$this->openFile();
		$data = call_user_func_array('sprintf', func_get_args());
		$data = $this->decorate($data);
		$this->write($data);
		#do not close the file handle
		#prevents lines from breaking when multiple instances are run
	}

	public function log($data)
	{
		$this->openFile();
		$data = call_user_func_array('sprintf', func_get_args());
		$data = $this->decorate($data);
		$data .= PHP_EOL;
		$this->write($data);
		$this->closeFile();
	}



	private function getAllFiles()
	{
		$files = glob(dirname($this->path) . DIRECTORY_SEPARATOR . '*');
		$files = array_filter($files, function($x)
		{
			return basename($x) != basename($this->path)
				and strpos($x, $this->baseName) !== false;
		});
		natcasesort($files);
		array_unshift($files, $this->path);
		return $files;
	}

	private function rotateIfNeeded()
	{
		if (filesize($this->path) > Config::$maxLogSize)
			$this->rotate();
	}

	private function rotate()
	{
		$files = $this->getAllFiles();
		$lastFile = end($files);
		if (preg_match('{^.*\.(\d+)(\.log)?$}', $lastFile, $matches))
		{
			$newLogNumber = intval($matches[1]) + 1;
		}
		else
		{
			$newLogNumber = 1;
		}
		$newPath = dirname($this->path) . DIRECTORY_SEPARATOR . $this->baseName . '.' . $newLogNumber . '.log';
		rename($this->path, $newPath);
	}
}
