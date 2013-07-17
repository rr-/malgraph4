<?php
class IndexControllerSearchModule extends AbstractControllerModule
{
	public static function getUrlParts()
	{
		return ['s/search'];
	}

	public static function url()
	{
		return '/s/search';
	}

	public static function work(&$viewContext)
	{
		$userName = $_POST['user-name'];
		if (!preg_match('#^' . UserController::getUserRegex() . '$#', $userName))
		{
			throw new Exception('Invalid characters');
		}
		$media = !empty($_POST['media']) ?: Media::Anime;
		$url = UserControllerProfileModule::url($userName, $media);
		$viewContext->layoutName = null;
		header('Location: ' . $url);
	}
}
