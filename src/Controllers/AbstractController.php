<?php
abstract class AbstractController
{
	public static function match($url)
	{
		throw new UnimplementedException();
	}

	public function work($url)
	{
		$this->doWork($url);
	}

	public abstract function doWork($url);
}
