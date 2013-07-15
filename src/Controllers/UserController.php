<?php
abstract class UserController extends AbstractController
{
	public static function moduleMatch()
	{
		throw new UnimplementedException();
	}

	public static function parseRequest($url, &$controllerContext)
	{
		$userRegex = '[0-9a-zA-Z_-]{2,}';

		$regex =
			'^/?' .
			'(' . $userRegex . ')' .
			'/?' .
			'(' .
				implode('|', (array) static::moduleMatch()) .
			')' .
			'(,(anime|manga))?' .
			'/?$';

		if (!preg_match('#' . $regex . '#', $url, $matches))
		{
			return false;
		}

		$controllerContext->userName = $matches[1];
		$media = isset($matches[3]) ? $matches[3] : 'anime';
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

	public function work($controllerContext)
	{
		parent::work($controllerContext);

		$queue = new Queue(Config::$userQueuePath);
		$queue->enqueue($controllerContext->userName);
	}
}
