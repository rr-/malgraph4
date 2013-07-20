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

	public function isUserMediaPrivate($media)
	{
		return $this->{Media::toString($media) . '_private'};
	}

	public static function getCount()
	{
		return R::getAll('SELECT COUNT(*) AS count FROM user')[0]['count'];
	}

	private static $visited;
	private function dfs($start, $incidenceList, &$cluster)
	{
		if (isset(self::$visited[$start]))
			return;
		if (!isset($incidenceList[$start]))
		{
			return;
		}
		$cluster []= $start;
		self::$visited[$start] = true;
		if (!empty($incidenceList[$start]))
		{
			foreach ($incidenceList[$start] as $newStart)
			{
				self::dfs($newStart, $incidenceList, $cluster);
			}
		}
	}

	public function getFranchisesFromUserMedia(array $entries)
	{
		$keysToFetch = [];
		$keysVisited = [];
		foreach ($entries as $entry)
		{
			$key = $entry->media . $entry->mal_id;
			$keysToFetch []= $key;
			$keysVisited []= $key;
		}

		$relations = [];
		while (!empty($keysToFetch))
		{
			$rows = [];
			foreach (array_chunk($keysToFetch, Config::$maxDbBindings) as $chunk)
			{
				$sql = 'SELECT (m.media || m.mal_id) AS key1, (mr.media || mr.mal_id) AS key2 FROM media m INNER JOIN mediarelation mr ON mr.media_id = m.id WHERE (m.media || m.mal_id) IN (' . R::genSlots($chunk) . ')';
				$rows = array_merge($rows, R::getAll($sql, $chunk));
			}
			$keysToFetch = [];
			foreach ($rows as $row)
			{
				$key1 = $row['key1'];
				$key2 = $row['key2'];
				if (!in_array($key1, $keysVisited))
				{
					$keysVisited []= $key1;
					$keysToFetch []= $key1;
				}
				if (!in_array($key2, $keysVisited))
				{
					$keysVisited []= $key2;
					$keysToFetch []= $key2;
				}
				if (!isset($relations[$key1]))
				{
					$relations[$key1] = [];
				}
				$relations[$key1] []= $key2;
			}
		}

		$relationsLeftSide = array_keys($relations);
		$relationsRightSide = empty($relations) ? [] : call_user_func_array('array_merge', $relations);
		$keysToFetch = array_merge($relationsLeftSide, $relationsRightSide);

		$ownEntries = [];
		foreach ($entries as $entry)
		{
			$key = $entry->media . $entry->mal_id;
			$ownEntries[$key] = $entry;
		}

		/*$allEntries = [];
		foreach (array_chunk($keysToFetch, Config::$maxDbBindings) as $chunk)
		{
			foreach (R::findAll('media', '(media || mal_id) IN (' . R::genSlots($chunk) . ')', $chunk) as $entry)
			{
				$key = $entry->media . $entry->mal_id;
				$allEntries[$key] = $entry;
			}
		}*/

		$clusters = [];
		$keysToProcess = $keysToFetch;
		while (!empty($keysToProcess))
		{
			$k = array_shift($keysToProcess);
			if (!isset($relations[$k]))
			{
				continue;
			}
			$cluster = [];
			self::dfs($k, $relations, $cluster);
			if (empty($cluster))
			{
				continue;
			}
			$clusters []= $cluster;
			foreach ($cluster as $k2)
			{
				unset($keysToProcess[$k2]);
			}
		}

		self::$visited = [];
		$franchises = [];
		foreach ($clusters as $cluster)
		{
			$franchise = new StdClass;
			$franchise->ownEntries = [];
			//$franchise->allEntries = [];
			foreach ($cluster as $key)
			{
				if (isset($ownEntries[$key]))
				{
					$franchise->ownEntries []= $ownEntries[$key];
				}
				/*if (isset($allEntries[$key]))
				{
					$franchise->allEntries []= $allEntries[$key];
				}*/
			}
			$franchises []= $franchise;
		}
		return $franchises;
	}
}
