<?php
class AdminControllerIndexModule extends AbstractControllerModule
{
	public static function getUrlParts()
	{
		return ['a'];
	}

	public static function url()
	{
		return '/a';
	}

	public static function work(&$controllerContext, &$viewContext)
	{
		$viewContext->viewName = 'admin-index';
		$viewContext->meta->title = 'MALgraph - admin';
		WebMediaHelper::addCustom($viewContext);
	}
}
