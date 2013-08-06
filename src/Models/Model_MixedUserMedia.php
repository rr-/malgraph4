<?php
class Model_MixedUserMedia
{
	public function __construct(array $columns)
	{
		foreach ($columns as $key => $value)
		{
			$this->$key = $value;
		}

		if ($this->media == Media::Manga)
		{
			$this->duration = 10;
		}

		$this->completed_duration = $this->duration;
		$this->completed_duration *= $this->media == Media::Anime
			? $this->episodes
			: $this->chapters;

		if (empty($this->title))
		{
			$this->title = 'Unknown ' . Media::toString($this->media) . ' entry #' . $this->mal_id;
		}

		$this->mal_link = 'http://myanimelist.net/' . Media::toString($this->media) . '/' . $this->mal_id;
	}

	public function getSeason()
	{
		$monthMap = [
			1 => 'winter',
			2 => 'winter',
			3 => 'spring',
			4 => 'spring',
			5 => 'spring',
			6 => 'summer',
			7 => 'summer',
			8 => 'summer',
			9 => 'fall',
			10 => 'fall',
			11 => 'fall',
			12 => 'winter',
		];

		$yearA = intval(substr($this->published_from, 0, 4));
		$yearB = intval(substr($this->published_to, 0, 4));
		$monthA = intval(substr($this->published_from, 6, 2));
		$monthB = intval(substr($this->published_to, 6, 2));
		if (!$yearA and !$yearB)
		{
			return null;
		}
		elseif (!$yearA)
		{
			if ($monthB)
			{
				return $monthMap[$monthB] . ' ' . $yearB;
			}
			return strval($yearB);
		}
		if ($monthA)
		{
			return $monthMap[$monthA] . ' ' . $yearA;
		}
		return strval($yearA);
	}


	public static function getFromIdList($list)
	{
		$allEntries = [];
		foreach (array_chunk($list, Config::$maxDbBindings) as $chunk)
		{
			$query = 'SELECT m.*, m.id AS media_id FROM media m WHERE m.media || m.mal_id IN (' . R::genSlots($chunk) . ')';
			$rows = R::getAll($query, $chunk);
			$entries = array_map(function($entry) { return new Model_MixedUserMedia($entry); }, $rows);
			$allEntries = array_merge($allEntries, $entries);
		}
		return $allEntries;
	}

	public static function getRatingDistribution($media, $doRecompute = false)
	{
		$dist = file_exists(Config::$globalsCachePath)
			? TextHelper::loadJson(Config::$globalsCachePath, true)
			: [];

		if (empty($dist) or $doRecompute)
		{
			$query = 'SELECT score, COUNT(score) AS count FROM usermedia WHERE media = ? GROUP BY score';
			$result = R::getAll($query, [$media]);
			$dist[$media] = [];
			foreach ($result as $row)
			{
				$count = $row['count'];
				$score = $row['score'];
				$dist[$media][$score] = $count;
			}
			TextHelper::putJson(Config::$globalsCachePath, $dist);
		}
		return RatingDistribution::fromArray($dist[$media]);
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

	public static function getFranchises(array $ownEntries, $loadEverything = false)
	{
		$ownClusters = self::clusterize($ownEntries);

		if ($loadEverything)
		{
			R::begin();
			$query = 'CREATE TEMPORARY TABLE hurr (franchise VARCHAR(10))';
			R::exec($query);
			foreach (array_chunk(array_keys($ownClusters), Config::$maxDbBindings) as $chunk)
			{
				$query = 'INSERT INTO hurr VALUES ' . join(',', array_fill(0, count($chunk), '(?)'));
				R::exec($query, $chunk);
			}
			$query = 'SELECT * FROM media INNER JOIN hurr ON media.franchise = hurr.franchise';
			$rows = R::getAll($query);
			$allEntries = array_map(function($entry) { return new Model_MixedUserMedia($entry); }, $rows);
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

	public static function attachGenres(array &$entries)
	{
		R::begin();
		R::exec('CREATE TEMPORARY TABLE hurr (media_id INTEGER)');
		foreach (array_chunk(array_map(function($entry) { return $entry->media_id; }, $entries), Config::$maxDbBindings) as $chunk)
		{
			R::exec('INSERT INTO hurr VALUES ' . join(',', array_fill(0, count($chunk), '(?)')), $chunk);
		}
		$data = R::getAll('SELECT * FROM mediagenre mg INNER JOIN hurr ON mg.media_id = hurr.media_id');
		R::rollback();

		$map = [];
		foreach ($entries as $entry)
		{
			$entry->genres = [];
			$map[$entry->media_id] = $entry;
		}

		foreach ($data as $row)
		{
			$row = ReflectionHelper::arrayToClass($row);
			if (!isset($map[$row->media_id]))
			{
				continue;
			}
			if (BanHelper::isGenreBanned($map[$row->media_id]->media, $row->mal_id))
			{
				continue;
			}
			$map[$row->media_id]->genres []= $row;
		}
	}

	public static function attachCreators(array &$entries)
	{
		R::begin();
		R::exec('CREATE TEMPORARY TABLE hurr (media_id INTEGER)');
		foreach (array_chunk(array_map(function($entry) { return $entry->media_id; }, $entries), Config::$maxDbBindings) as $chunk)
		{
			R::exec('INSERT INTO hurr VALUES ' . join(',', array_fill(0, count($chunk), '(?)')), $chunk);
		}
		switch (reset($entries)->media)
		{
			case Media::Anime:
				$table = 'animeproducer';
				break;
			case Media::Manga:
				$table = 'mangaauthor';
				break;
			default:
				throw new BadMediaException();
		}
		$rows = R::getAll('SELECT * FROM ' . $table . ' mc INNER JOIN hurr ON mc.media_id = hurr.media_id');
		$data = ReflectionHelper::arraysToClasses($rows);
		R::rollback();

		$map = [];
		foreach ($entries as $entry)
		{
			$entry->creators = [];
			$map[$entry->media_id] = $entry;
		}

		foreach ($data as $row)
		{
			if (!isset($map[$row->media_id]))
			{
				continue;
			}
			if (BanHelper::isCreatorBanned($map[$row->media_id]->media, $row->mal_id))
			{
				continue;
			}
			$map[$row->media_id]->creators []= $row;
		}
	}

}
