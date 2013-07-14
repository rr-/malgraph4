<?php
class IndexController extends AbstractController
{
	public static function match($url)
	{
		return $url == '/' or $url == '';
	}

	public function doWork($url)
	{
		echo 'I\'m index';
	}
}
