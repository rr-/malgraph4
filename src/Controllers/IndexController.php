<?php
class IndexController extends AbstractController
{
	public static function parseRequest($url, &$controllerContext)
	{
		return $url == '/' or $url == '';
	}

	public static function getViewName()
	{
		return 'index';
	}

	public function doWork($controllerContext, &$viewContext)
	{
		$viewContext->variable = mt_rand(0, 1);
	}
}
