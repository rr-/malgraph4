<?php
class UserControllerProfileModule extends AbstractUserControllerModule
{
	public static function getText()
	{
		return 'Profile';
	}

	public static function getUrlParts()
	{
		return ['', 'profile'];
	}

	public static function getMediaAvailability()
	{
		return [];
	}

	public static function work(&$viewContext)
	{
		$viewContext->viewName = 'user-profile';
	}
}
