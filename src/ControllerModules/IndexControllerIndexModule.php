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
		$viewContext->viewName = 'index-index';
		$viewContext->meta->keywords = array_merge($viewContext->meta->keywords, ['myanimelist', 'mal', 'rating', 'favorites', 'score']);
		WebMediaHelper::addCustom($viewContext);
	}
}
