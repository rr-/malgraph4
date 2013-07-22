<?php
class IndexControllerIndexModule extends AbstractControllerModule
{
	public static function getUrlParts()
	{
		return ['', 'index'];
	}

	public static function url()
	{
		return '/';
	}

	public static function work(&$viewContext)
	{
		$viewContext->viewName = 'index';
		$viewContext->meta->styles []= '/media/css/index/index.css';
		$viewContext->meta->scripts []= '/media/js/index/index.js';
		$viewContext->meta->keywords = array_merge($viewContext->meta->keywords, ['myanimelist', 'mal', 'rating', 'favorites', 'score']);
	}
}
