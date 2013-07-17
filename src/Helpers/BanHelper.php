<?php
class BanHelper
{
	public static function isBanned($userName)
	{
		$x = strtolower(file_get_contents(Config::$banListPath));
		$lines = explode("\n", str_replace("\r", '', $x));
		return in_array(strtolower($userName), $lines);
	}
}
