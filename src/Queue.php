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

	public function peek()
	{
		$this->open();
		$lines = $this->readLines();
		$this->close();
		return array_shift($lines) ?: null;
	}

	public function seek($string)
	{
		$this->open();
		$lines = $this->readLines();
		$this->close();
		$index = array_search($string, $lines);
		return $index !== false ? $index + 1 : false;
	}

	public function dequeue()
	{
		$this->open();
		$lines = $this->readLines();
		$firstLine = array_shift($lines);
		$this->writeLines($lines);
		$this->close();
		return $firstLine ?: null;
	}

	public function enqueue($string)
	{
		$this->open();
		$lines = $this->readLines();
		$index = array_search($string, $lines);
		if ($index === false)
		{
			$index = count($lines);
			$lines []= $string;
			$this->writeLines($lines);
		}
		$this->close();
		return $index !== false ? $index + 1 : false;
	}

	public function size()
	{
		$this->open();
		$lines = $this->readLines();
		$this->close();
		return count($lines);
	}
}
