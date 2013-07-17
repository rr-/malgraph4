<?php
class IndexController extends AbstractController
{
	public static function parseRequest($url, &$controllerContext)
	{
		return $url == '/' or $url == '';
	}

	public static function work($controllerContext, &$viewContext)
	{
		$viewContext->viewName = 'index';
		$viewContext->meta->styles []= '/media/css/index/index.css';
		$viewContext->meta->scripts []= '/media/js/index/index.js';
	}
}
