<?php
class Queue
{
	private $lines = [];
	private $file = null;

	public function __construct($file)
	{
		$this->file = $file;
	}

	public function dequeue()
	{
		$fh = fopen($this->file, 'r+b');
		flock($fh, LOCK_EX);

		fseek($fh, 0, SEEK_END);
		$size = ftell($fh);
		fseek($fh, 0, SEEK_SET);
		$data = $size > 0
			? fread($fh, $size)
			: null;

		$lines = explode("\n", $data);
		$firstLine = array_shift($lines);
		$data = join("\n", $lines);

		fseek($fh, 0, SEEK_SET);
		ftruncate($fh, strlen($data));
		fwrite($fh, $data);

		fclose($fh);
		return $firstLine ?: null;
	}

	public function enqueue($string)
	{
		$fh = fopen($this->file, 'r+b');
		flock($fh, LOCK_EX);

		fseek($fh, 0, SEEK_END);
		$size = ftell($fh);
		fseek($fh, 0, SEEK_SET);
		$data = $size > 0
			? fread($fh, $size)
			: null;

		$lines = explode("\n", $data);

		if (!in_array($string, $lines))
		{
			fseek($fh, 0, SEEK_END);
			fwrite($fh, $string . "\n");
		}
		fclose($fh);
	}
}
