<?php
class UserControllerListsModule extends AbstractUserControllerModule
{
	public static function getText()
	{
		return 'List';
	}

	public static function getUrlParts()
	{
		return ['list', 'lists'];
	}

	public static function getMediaAvailability()
	{
		return [Media::Anime, Media::Manga];
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

	public static function work(&$viewContext)
	{
		$list = self::getUserList($viewContext->userId, $viewContext->media);
		$viewContext->list = $list;
	}
}
