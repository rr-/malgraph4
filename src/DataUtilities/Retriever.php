<?php
class Retriever
{
	public static function getUserMediaList($userId, $media)
	{
		$pdo = Database::getPDO();
		$stmt = $pdo->prepare('SELECT ' .
			'uml.score, uml.status, m.title, uml.media, uml.mal_id ' .
			'FROM user_media_list uml ' .
			'LEFT JOIN media m ON uml.mal_id = m.mal_id ' .
			'AND uml.media = m.media ' .
			'WHERE uml.user_id = ? AND uml.media = ?');
		$stmt->execute([$userId, $media]);
		return $stmt->fetchAll();
	}

	public static function isUserMediaListPrivate($userId, $media)
	{
		$pdo = Database::getPDO();
		$stmt = $pdo->prepare('SELECT ' .
			Media::toString($media) . '_private AS private ' .
			'FROM users WHERE user_id = ?');
		$stmt->execute([$userId]);
		return $stmt->fetch()->private;
	}

	public static function getMediaTitle($row)
	{
		if (!empty($row->title))
		{
			return $row->title;
		}
		return 'Unknown ' . Media::toString($row->media) . ' entry #' . $row->mal_id;
	}
}
