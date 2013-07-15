<?php
class IndexController extends AbstractController
{
	public static function parseRequest($url, &$controllerContext)
	{
		return $url == '/' or $url == '';
	}

	public static function work($controllerContext, &$viewContext)
	{
		$viewContext->name = 'index';
		$viewContext->variable = mt_rand(0, 1);
	}
}
