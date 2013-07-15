<?php
class UserProfileController extends UserController
{
	public static function moduleMatch()
	{
		return ['', 'profile'];
	}

	public static function getViewName()
	{
		return 'user-profile';
	}

	public function doWork($controllerContext, &$viewContext)
	{
		$viewContext->userName = $controllerContext->userName;
		$viewContext->media = $controllerContext->media;
	}
}
