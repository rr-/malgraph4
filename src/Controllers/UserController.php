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
				'|/profile|/list|/rati|/acti|/favs|/sug|/achi' .
			')' .
			'(,(anime|manga))?' .
			'/?$';

		if (!preg_match('#' . $regex . '#', $url, $matches))
		{
			return false;
		}

		$controllerContext->userName = $matches[1];
		$media = isset($matches[4]) ? $matches[4] : 'anime';
		switch ($media)
		{
			case 'anime': $controllerContext->media = Media::Anime; break;
			case 'manga': $controllerContext->media = Media::Manga; break;
			default: throw new BadMediaException();
		}
		$module = ltrim($matches[2], '/') ?: 'profile';
		$controllerContext->rawModule = $module;
		switch ($module)
		{
			case 'profile':      $controllerContext->module = UserModule::Profile; break;
			case 'list':         $controllerContext->module = UserModule::Lists; break;
			case 'ratings':      $controllerContext->module = UserModule::Ratings; break;
			case 'activity':     $controllerContext->module = UserModule::Activity; break;
			case 'favorites':    $controllerContext->module = UserModule::Favorites; break;
			case 'suggestions':  $controllerContext->module = UserModule::Suggestions; break;
			case 'achievements': $controllerContext->module = UserModule::Achievements; break;
			default: throw new BadUserModuleException();
		}
		return true;
	}

	public static function work($controllerContext, &$viewContext)
	{
		$viewContext->viewName = 'user-' . $controllerContext->rawModule;
		$viewContext->module = $controllerContext->module;
		$viewContext->userName = $controllerContext->userName;
		$viewContext->media = $controllerContext->media;

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

		$methodNames =
		[
			UserModule::Profile => 'profile',
			UserModule::Lists => 'lists',
			UserModule::Ratings => 'ratings',
			UserModule::Activity => 'activity',
			UserModule::Favorites => 'favorites',
			UserModule::Suggestions => 'suggestions',
			UserModule::Achievements => 'achievements',
		];
		$methodName = 'action' . ucfirst($methodNames[$controllerContext->module]);
		self::$methodName($viewContext);
	}

	private static function getUserList($userId, $media)
	{
		$pdo = Database::getPDO();
		$stmt = $pdo->prepare('SELECT * FROM user_media_list ' .
			'INNER JOIN media ON user_media_list.mal_id = media.mal_id ' .
			'AND user_media_list.media = media.media ' .
			'WHERE user_id = ? AND user_media_list.media = ?');
		$stmt->execute([$userId, $media]);
		return $stmt->fetchAll();
	}



	public static function actionProfile(&$viewContext)
	{
	}



	public static function actionLists(&$viewContext)
	{
		$list = self::getUserList($viewContext->userId, $viewContext->media);
		$viewContext->list = $list;
	}
}
