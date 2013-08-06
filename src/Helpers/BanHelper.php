<?php
class BanHelper extends Singleton
{
	private static $bannedUsers;
	private static $bannedGenres;
	private static $bannedCreators;
	private static $bannedGenresForRecs;
	private static $bannedFranchiseCoupling;

	public static function doInit()
	{
		self::$bannedUsers = array_map('strtolower', TextHelper::loadSimpleList(Config::$bannedUsersListPath));
		self::$bannedGenres = TextHelper::loadSimpleList(Config::$bannedGenresListPath);
		self::$bannedCreators = TextHelper::loadSimpleList(Config::$bannedCreatorsListPath);
		self::$bannedGenresForRecs = TextHelper::loadSimpleList(Config::$bannedGenresForRecsListPath);
		self::$bannedFranchiseCoupling = self::loadBannedFranchiseCoupling();
	}

	private static function loadBannedFranchiseCoupling()
	{
		$lines = TextHelper::loadSimpleList(Config::$bannedFranchiseCouplingListPath);
		$lines []= '---';
		$groups = [];

		$reset = true;
		$key = 0;
		$group = [];
		foreach ($lines as $line)
		{
			if (preg_match('/^-+$/', $line))
			{
				if (!empty($group))
				{
					$groups[$key] = $group;
					++ $key;
				}
				$group = [];
				continue;
			}
			$group []= $line;
		}

		$ret = [];
		foreach ($groups as $group)
		{
			foreach ($group as $key)
			{
				$ret[$key] = array_combine($group, array_fill(0, count($group), true));
				unset($ret[$key][$key]);
			}
		}

		return $ret;
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

	public static function isFranchiseCouplingBanned($media1, $malId1, $media2, $malId2)
	{
		$key1 = $media1 . $malId1;
		$key2 = $media2 . $malId2;
		return isset(self::$bannedFranchiseCoupling[$key1][$key2]);
	}
}

BanHelper::init();
