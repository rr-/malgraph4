<?php
class IndexControllerAboutModule extends AbstractControllerModule
{
	public static function getUrlParts()
	{
		return ['s/about'];
	}

	public static function url()
	{
		return '/s/about';
	}

	public static function work(&$viewContext)
	{
		$viewContext->viewName = 'about';
		$viewContext->meta->title = 'MALgraph - about';
	}
}
