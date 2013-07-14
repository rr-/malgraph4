<?php
abstract class UserController extends AbstractController
{
	public static function moduleMatch()
	{
		throw new UnimplementedException();
	}

	public static function match($url)
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

		return preg_match('#' . $regex . '#', $url);
	}

	public function doWork($url)
	{
		$url = trim($url, '/');
		$url = strtr($url, '/,', '::');
		$tmp = explode(':', $url);
		$user = array_shift($tmp);
		$module = array_shift($tmp);
		$media = array_shift($tmp) ?: 'anime';
		switch ($media)
		{
			case 'anime':
				$media = Media::Anime;
				break;
			case 'manga':
				$media = Media::Manga;
				break;
			default:
				throw new BadMediaException();
		}

		$this->user = $user;
		$this->media = $media;

		$queue = new Queue(Config::$userQueuePath);
		$queue->enqueue($user);
	}
}
