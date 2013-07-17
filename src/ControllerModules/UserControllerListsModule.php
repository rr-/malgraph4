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
	}

	public static function work(&$viewContext)
	{
		$viewContext->viewName = 'user-list';
		$viewContext->meta->styles []= '/media/css/user/list.css';
		$viewContext->meta->scripts []= 'http://cdn.ucb.org.br/Scripts/tablesorter/jquery.tablesorter.min.js';
		$viewContext->meta->scripts []= '/media/js/user/list.js';

		$pdo = Database::getPDO();
		$stmt = $pdo->prepare('SELECT ' .
			'uml.score, uml.status, m.title, uml.media, uml.mal_id ' .
			'FROM user_media_list uml ' .
			'LEFT JOIN media m ON uml.mal_id = m.mal_id ' .
			'AND uml.media = m.media ' .
			'WHERE uml.user_id = ? AND uml.media = ?');
		$stmt->execute([$viewContext->userId, $viewContext->media]);
		$viewContext->list = $stmt->fetchAll();

		$stmt = $pdo->prepare('SELECT ' .
			Media::toString($viewContext->media) . '_private AS private ' .
			'FROM users WHERE user_id = ?');
		$stmt->execute([$viewContext->userId]);
		$viewContext->private = $stmt->fetch()->private;
	}
}
