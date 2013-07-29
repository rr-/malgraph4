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
		$userName = trim($userName);
		$media = !empty($_POST['media']) ?: Media::Anime;

		if (empty($userName))
		{
			$viewContext->layoutName = null;
			$url = IndexControllerIndexModule::url($userName, $media);
			HttpHeadersHelper::setCurrentHeader('Location', $url);
			return;
		}

		if (!preg_match('#^' . UserController::getUserRegex() . '$#', $userName))
		{
			$viewContext->viewName = 'error-user-invalid';
			return;
		}

		$viewContext->layoutName = null;
		$url = UserControllerProfileModule::url($userName, $media);
		HttpHeadersHelper::setCurrentHeader('Location', $url);
	}
}
