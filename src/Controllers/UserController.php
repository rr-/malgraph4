<?php
class UserController extends AbstractController
{
	public static function getUserRegex()
	{
		return '[0-9a-zA-Z_-]{2,}';
	}

	public static function parseRequest($url, &$controllerContext)
	{
		$userRegex = self::getUserRegex();
		$modulesRegex = self::getAvailableModulesRegex();
		$mediaParts = array_map(['Media', 'toString'], Media::getConstList());
		$mediaRegex = implode('|', $mediaParts);

		$regex =
			'^/?' .
			'(' . $userRegex . ')' .
			'(' . $modulesRegex . ')' .
			'(,(' . $mediaRegex . '))?' .
			'/?($|\?)';

		if (!preg_match('#' . $regex . '#', $url, $matches))
		{
			return false;
		}

		$controllerContext->userName = $matches[1];
		$media = !empty($matches[4]) ? $matches[4] : 'anime';
		switch ($media)
		{
			case 'anime': $controllerContext->media = Media::Anime; break;
			case 'manga': $controllerContext->media = Media::Manga; break;
			default: throw new BadMediaException();
		}
		$rawModule = ltrim($matches[2], '/') ?: 'profile';
		$controllerContext->rawModule = $rawModule;
		$controllerContext->module = self::getModuleByUrlPart($rawModule);
		assert(!empty($controllerContext->module));
		return true;
	}

	public static function work($controllerContext, &$viewContext)
	{
		$viewContext->media = $controllerContext->media;
		$viewContext->module = $controllerContext->module;

		if (BanHelper::isUserBanned($controllerContext->userName))
		{
			$viewContext->userName = $controllerContext->userName;
			$viewContext->viewName = 'error-user-blocked';
			$viewContext->meta->title = 'MALgraph - user blocked';
			return;
		}

		$queue = new Queue(Config::$userQueuePath);
		$queuePosition = $queue->enqueue($controllerContext->userName);

		$user = R::findOne('user', 'LOWER(name) = LOWER(?)', [$controllerContext->userName]);
		if (empty($user))
		{
			$viewContext->queuePosition = $queuePosition;
			$viewContext->userName = $controllerContext->userName;
			$viewContext->viewName = 'error-user-enqueued';
			$viewContext->meta->title = 'MALgraph - user enqueued';
			return;
		}
		$viewContext->user = $user;
		$viewContext->meta->styles []= '/media/css/menu.css';
		$viewContext->meta->styles []= '/media/css/user/general.css';

		assert(!empty($controllerContext->module));
		$module = $controllerContext->module;
		$module::work($viewContext);
		$viewContext->userMenu = true;
	}
}
