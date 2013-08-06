<?php
class BanHelper extends Singleton
{
	private static $bannedUsers;
	private static $bannedGenres;
	private static $bannedCreators;
	private static $bannedGenresForRecs;

	public static function doInit()
	{
		self::$bannedUsers = array_map('strtolower', TextHelper::loadSimpleList(Config::$bannedUsersListPath));
		self::$bannedGenres = TextHelper::loadSimpleList(Config::$bannedGenresListPath);
		self::$bannedCreators = TextHelper::loadSimpleList(Config::$bannedCreatorsListPath);
		self::$bannedGenresForRecs = TextHelper::loadSimpleList(Config::$bannedGenresForRecsListPath);
	}

	public static function isUserBanned($userName)
	{
		return in_array(strtolower($userName), self::$bannedUsers);
	}

	public static function isGenreBanned($media, $genreId)
	{
		return in_array($media . $genreId, self::$bannedGenres);
	}

	public static function isCreatorBanned($media, $creatorId)
	{
		return in_array($media . $creatorId, self::$bannedCreators);
	}

	public static function isGenreBannedForRecs($media, $genreId)
	{
		return in_array($media . $genreId, self::$bannedGenresForRecs);
	}
}

BanHelper::init();
