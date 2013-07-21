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
		$query = 'SELECT m.*, um.* FROM usermedia um LEFT JOIN media m ON m.media = um.media AND m.mal_id = um.mal_id WHERE um.user_id = ? AND um.media = ?';
		$rows = R::getAll($query, [$this->id, $media]);
		$result = array_map(function($row) { return new Model_MixedUserMedia($row); }, $rows);
		return $result;
	}

	public function getFriends()
	{
		$result = [];
		foreach (R::getAll('SELECT * FROM userfriend WHERE user_id = ?', [$this->id]) as $row)
		{
			$x = new StdClass;
			foreach ($row as $key=>$val)
			{
				$x->$key = $val;
			}
			$result []= $x;
		}
		return $result;
	}

	public function getClubs()
	{
		$result = [];
		foreach (R::getAll('SELECT * FROM userclub WHERE user_id = ?', [$this->id]) as $row)
		{
			$x = new StdClass;
			foreach ($row as $key=>$val)
			{
				$x->$key = $val;
			}
			$result []= $x;
		}
		return $result;
	}

	public function isUserMediaPrivate($media)
	{
		return $this->{Media::toString($media) . '_private'};
	}

	public static function getCount()
	{
		return R::getAll('SELECT COUNT(*) AS count FROM user')[0]['count'];
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
			R::exec('CREATE TEMPORARY TABLE hurr (franchise VARCHAR(10))');
			foreach (array_chunk(array_keys($ownClusters), Config::$maxDbBindings) as $chunk)
			{
				R::exec('INSERT INTO hurr VALUES ' . join(',',array_fill(0, count($chunk), '(?)')), $chunk);
			}
			$allEntries = R::getAll('SELECT * FROM media INNER JOIN hurr ON media.franchise = hurr.franchise');
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
