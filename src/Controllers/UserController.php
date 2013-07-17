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
			'/?$';

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
		$viewContext->viewName = 'user-' . $controllerContext->rawModule;
		$viewContext->module = $controllerContext->module;
		$viewContext->userName = $controllerContext->userName;
		$viewContext->media = $controllerContext->media;

		if (BanHelper::isBanned($viewContext->userName))
		{
			throw new Exception('user is banned, can\'t show you anything');
		}

		$pdo = Database::getPDO();
		$stmt = $pdo->prepare('SELECT * FROM users WHERE LOWER(name) = LOWER(?)');
		$stmt->Execute([$viewContext->userName]);
		$result = $stmt->fetch();
		if (empty($result))
		{
			#todo:
			throw new Exception('user doesn\'t exist in db, but he just got enqueued');
		}
		$viewContext->userId = $result->user_id;

		$queue = new Queue(Config::$userQueuePath);
		$queue->enqueue($controllerContext->userName);

		assert(!empty($controllerContext->module));
		$module = $controllerContext->module;
		$module::work($viewContext);
		$viewContext->userMenu = true;
	}
}
