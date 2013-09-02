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

	public static function work(&$controllerContext, &$viewContext)
	{
		$viewContext->viewName = 'index-index';
		$viewContext->layoutName = 'layout-headerless';
		$viewContext->meta->keywords = array_merge($viewContext->meta->keywords, ['myanimelist', 'mal', 'rating', 'favorites', 'score']);
		WebMediaHelper::addCustom($viewContext);
	}
}
