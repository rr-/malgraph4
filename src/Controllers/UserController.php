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

	public static function work($controllerContext, &$viewContext)
	{
		$viewContext->userName = $controllerContext->userName;
		$viewContext->media = $controllerContext->media;
		$viewContext->name = 'user-' . $controllerContext->module;

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

		$methodName = 'action' . ucfirst($controllerContext->module);
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

	public static function actionList(&$viewContext)
	{
		$list = self::getUserList($viewContext->userId, $viewContext->media);
		$viewContext->list = $list;
	}
}
