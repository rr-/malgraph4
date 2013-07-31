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

	private static $ratingDistributionCache;
	public static function getRatingDistribution($media)
	{
		if (self::$ratingDistributionCache === null)
		{
			$query = 'SELECT media, score, COUNT(score) AS count FROM usermedia GROUP BY media, score';
			self::$ratingDistributionCache = R::getAll($query);
		}
		$result = self::$ratingDistributionCache;
		$dist = [];
		foreach ($result as $row)
		{
			if ($row['media'] != $media)
			{
				continue;
			}
			$count = $row['count'];
			$score = $row['score'];
			$dist[$score] = $count;
		}
		return RatingDistribution::fromArray($dist);
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
}
