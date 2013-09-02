<?php
class IndexControllerPrivacyModule extends AbstractControllerModule
{
	public static function getUrlParts()
	{
		return ['s/privacy'];
	}

	public static function url()
	{
		return '/s/privacy';
	}

	public static function getView()
	{
		return 'privacy';
	}

	public static function work(&$controllerContext, &$viewContext)
	{
		$viewContext->viewName = 'index-privacy';
		$viewContext->meta->title = 'MALgraph - privacy policy';
		WebMediaHelper::addCustom($viewContext);
	}
}
