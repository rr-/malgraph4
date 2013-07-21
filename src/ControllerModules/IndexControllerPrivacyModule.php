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

	public static function work(&$viewContext)
	{
		$viewContext->viewName = 'privacy';
		$viewContext->meta->title = 'MALgraph - privacy policy';
	}
}
