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
		$viewContext->layoutName = null;
		$userName = $_POST['user-name'];
		$media = !empty($_POST['media']) ?: Media::Anime;
		$url = UserControllerProfileModule::url($userName, $media);
		header('Location: ' . $url);
	}
}
