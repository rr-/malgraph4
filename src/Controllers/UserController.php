<?php
class UserController extends AbstractController
{
	public static function parseRequest($url, &$controllerContext)
	{
		$userRegex = '[0-9a-zA-Z_-]{2,}';

		$regex =
			'^/?' .
			'(' . $userRegex . ')' .
			'(' .
				'|/profile|/list' .
			')' .
			'(,(anime|manga))?' .
			'/?$';

		if (!preg_match('#' . $regex . '#', $url, $matches))
		{
			return false;
		}

		$controllerContext->userName = $matches[1];
		$controllerContext->module = ltrim($matches[2], '/') ?: 'profile';
		$media = isset($matches[4]) ? $matches[4] : 'anime';
		switch ($media)
		{
			case 'anime':
				$controllerContext->media = Media::Anime;
				break;
			case 'manga':
				$controllerContext->media = Media::Manga;
				break;
			default:
				throw new BadMediaException();
		}
		return true;
	}

	public function doWork($controllerContext, &$viewContext)
	{
		$viewContext->userName = $controllerContext->userName;
		$viewContext->media = $controllerContext->media;
		$viewContext->name = 'user-' . $controllerContext->module;

		$queue = new Queue(Config::$userQueuePath);
		$queue->enqueue($controllerContext->userName);

		$methodName = 'action' . ucfirst($controllerContext->module);
		$this->$methodName($viewContext);
	}

	public function actionProfile(&$viewContext)
	{
	}
}
