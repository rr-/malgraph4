<?php
class IndexControllerGlobalsModule extends AbstractControllerModule
{
	public static function getUrlParts()
	{
		return ['s/globals'];
	}

	public static function url()
	{
		return '/s/globals';
	}

	public static function work(&$viewContext)
	{
		$viewContext->viewName = 'globals';
		$viewContext->meta->title = 'MALgraph - global statistics';
		$viewContext->meta->styles []= '/media/css/narrow.css';
	}
}
