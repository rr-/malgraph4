<?php
class BanHelper
{
	public static function isUserBanned($userName)
	{
		$lines = TextHelper::loadSimpleList(Config::$bannedUsersListPath);
		$lines = array_map('strtolower', $lines);
		return in_array(strtolower($userName), $lines);
	}

	public static function isGenreBanned($media, $genreId)
	{
		$lines = TextHelper::loadSimpleList(Config::$bannedGenresListPath);
		return in_array($media . $genreId, $lines);
	}

	public static function isCreatorBanned($media, $creatorId)
	{
		$lines = TextHelper::loadSimpleList(Config::$bannedCreatorsListPath);
		return in_array($media . $creatorId, $lines);
	}
}
