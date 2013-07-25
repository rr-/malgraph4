<?php
class MediaGenreDistribution extends AbstractDistribution
{
	public function getNullGroupKey()
	{
		return 0;
	}

	public static function fromEntries(array $entries = [])
	{
		$dist = new self();
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

		foreach ($map as $entry)
		{
			$dist->addEntry($entry);
		}
		$dist->finalize();
		return $dist;
	}

	public function addEntry($entry)
	{
		foreach ($entry->genres as $genre)
		{
			$this->addToGroup($genre, $entry);
		}
	}
}
