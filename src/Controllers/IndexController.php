<?php
class IndexController extends AbstractController
{
	public static function parseRequest($url, &$controllerContext)
	{
		return $url == '/' or $url == '';
	}

	public function doWork($controllerContext, &$viewContext)
	{
		$viewContext->name = 'index';
		$viewContext->variable = mt_rand(0, 1);
	}
}
