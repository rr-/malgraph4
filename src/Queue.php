<?php
class Queue
{
	private $lines = [];
	private $file = null;
	private $handle = null;

	private function open()
	{
		$this->handle = fopen($this->file, 'r+b');
		flock($this->handle, LOCK_EX);
	}

	private function readLines()
	{
		assert($this->handle != null);
		fseek($this->handle, 0, SEEK_END);
		$size = ftell($this->handle);
		fseek($this->handle, 0, SEEK_SET);
		$data = $size > 0
			? fread($this->handle, $size)
			: null;
		$lines = explode("\n", $data);
		$lines = array_filter($lines);
		return $lines;
	}

	private function writeLines($lines)
	{
		$data = join("\n", $lines);
		fseek($this->handle, 0, SEEK_SET);
		ftruncate($this->handle, strlen($data));
		fwrite($this->handle, $data);
	}

	private function close()
	{
		fclose($this->handle);
		$this->handle = null;
	}

	public function __construct($file)
	{
		$this->file = $file;
	}

	public function seek($string)
	{
		$this->open();
		$lines = $this->readLines();
		$this->close();
		$index = array_search($string, $lines);
		return $index !== false ? $index + 1 : false;
	}

	private function _dequeue($num, $doWrite)
	{
		$this->open();
		$lines = $this->readLines();
		$return = [];
		foreach (range(1, $num === null ? 1 : $num) as $i)
		{
			$line = array_shift($lines);
			if ($line)
			{
				$return []= $line;
			}
		}
		if ($doWrite)
		{
			$this->writeLines($lines);
		}
		$this->close();
		return $num !== null
			? $return
			: reset($return);
	}

	public function peek($num = null)
	{
		return $this->_dequeue($num, false);
	}

	public function dequeue($num = null)
	{
		return $this->_dequeue($num, true);
	}

	public function enqueue($newLines)
	{
		$this->open();
		$lines = $this->readLines();
		$indexes = [];
		foreach ((array) $newLines as $newLine)
		{
			$index = array_search($newLine, $lines);
			if ($index === false)
			{
				$index = count($lines);
				$lines []= $newLine;
				$this->writeLines($lines);
			}
			$indexes []= $index + 1;
		}
		$this->close();
		return is_array($newLines)
			? $indexes
			: reset($indexes);
	}

	public function size()
	{
		$this->open();
		$lines = $this->readLines();
		$this->close();
		return count($lines);
	}
}
