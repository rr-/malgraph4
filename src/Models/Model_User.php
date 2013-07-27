<?php
R::dependencies([
	'userfriend' => ['user'],
	'userclub' => ['user'],
	'usermedia' => ['user'],
	'userhistory' => ['user'],
]);

class Model_User extends RedBean_SimpleModel
{
	public function getMixedUserMedia($media)
	{
		$query = 'SELECT m.*, um.*, m.id AS media_id FROM usermedia um' .
			' LEFT JOIN media m ON m.media = um.media AND m.mal_id = um.mal_id' .
			' WHERE um.user_id = ? AND um.media = ?';
		$rows = R::getAll($query, [$this->id, $media]);
		$result = array_map(function($row) { return new Model_MixedUserMedia($row); }, $rows);
		return $result;
	}

	public function getFriends()
	{
		$query = 'SELECT * FROM userfriend' .
			' WHERE user_id = ?' .
			' ORDER BY name COLLATE NOCASE ASC';
		$rows = R::getAll($query, [$this->id]);
		$result = array_map(function($row) { return ReflectionHelper::arrayToClass($row); }, $rows);
		return $result;
	}

	public function getClubs()
	{
		$query = 'SELECT * FROM userclub' .
			' WHERE user_id = ?' .
			' ORDER BY name COLLATE NOCASE ASC';
		$rows = R::getAll($query, [$this->id]);
		$result = array_map(function($row) { return ReflectionHelper::arrayToClass($row); }, $rows);
		return $result;
	}

	public function getHistory($media)
	{
		$result = [];
		$query = 'SELECT m.*, uh.*, m.id AS media_id FROM userhistory uh' .
			' LEFT JOIN media m ON m.media = uh.media AND m.mal_id = uh.mal_id' .
			' WHERE uh.user_id = ? AND uh.media = ?' .
			' ORDER BY timestamp DESC';
		$rows = R::getAll($query, [$this->id, $media]);
		$result = array_map(function($row) { return new Model_MixedUserMedia($row); }, $rows);
		return $result;
	}

	public function isUserMediaPrivate($media)
	{
		return $this->{Media::toString($media) . '_private'};
	}

	public static function getCount()
	{
		$query = 'SELECT COUNT(*) AS count FROM user';
		return R::getAll($query)[0]['count'];
	}

	public function getMismatchedUserMedia(array $entries)
	{
		$entriesMismatched = [];
		foreach ($entries as $entry)
		{
			if ($entry->media == Media::Anime)
			{
				$a = $entry->finished_episodes;
				$b = $entry->episodes;
			} else {
				$a = $entry->finished_chapters;
				$b = $entry->chapters;
			}
			if ($a != $b and ($b > 0 or $entry->publishing_status == MediaStatus::Publishing) and $entry->status == UserListStatus::Finished)
			{
				$entriesMismatched []= $entry;
			}
		}
		return $entriesMismatched;
	}

	/**
	* Map entries to dictionary of franchise->entries
	*/
	private static function clusterize($entries)
	{
		$clusters = [];
		foreach ($entries as $entry)
		{
			if (!isset($clusters[$entry->franchise]))
			{
				$clusters[$entry->franchise] = [];
			}
			$clusters[$entry->franchise] []= $entry;
		}
		return $clusters;
	}

	public function getFranchisesFromUserMedia(array $ownEntries, $loadEverything = false)
	{
		$ownClusters = self::clusterize($ownEntries);

		if ($loadEverything)
		{
			R::begin();
			$query = 'CREATE TEMPORARY TABLE hurr (franchise VARCHAR(10))';
			R::exec();
			foreach (array_chunk(array_keys($ownClusters), Config::$maxDbBindings) as $chunk)
			{
				$query = 'INSERT INTO hurr VALUES ' . join(',', array_fill(0, count($chunk), '(?)'));
				R::exec($query, $chunk);
			}
			$query = 'SELECT * FROM media INNER JOIN hurr ON media.franchise = hurr.franchise';
			$allEntries = R::getAll($query);
			$allEntries = array_map(function($entry) { return new Model_MixedUserMedia($entry); }, $allEntries);
			R::rollback();

			$allClusters = self::clusterize($allEntries);
		}

		$franchises = [];
		foreach ($ownClusters as $key => $ownCluster)
		{
			$franchise = new StdClass;
			$franchise->allEntries =
				!empty($allClusters[$key])
				? $allClusters[$key]
				: [];
			$franchise->ownEntries = array_values($ownCluster);
			$franchises []= $franchise;
		}
		return $franchises;
	}
}
