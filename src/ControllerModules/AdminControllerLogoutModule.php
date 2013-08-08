<?php
class AdminControllerLogoutModule extends AbstractControllerModule
{
	public static function getUrlParts()
	{
		return ['a/logout'];
	}

	public static function url()
	{
		return '/a/logout';
	}

	public static function work(&$viewContext)
	{
		$viewContext->viewName = null;
		unset($_SESSION['logged-in']);
		$url = AdminControllerLoginModule::url();
		HttpHeadersHelper::setCurrentHeader('Location', $url);
	}
}
