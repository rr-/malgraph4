<?php
class MediaCreatorDistribution extends AbstractDistribution
{
	public function getNullGroupKey()
	{
		return 0;
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
			$map[$entry->media_id] = &$entry;
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

	public static function fromEntries(array $entries = [])
	{
		$dist = new self();
		if (!empty($entries))
		{
			self::attachCreators($entries);
			foreach ($entries as $entry)
			{
				$dist->addEntry($entry);
			}
		}
		$dist->finalize();
		return $dist;
	}

	protected function addEntry($entry)
	{
		foreach ($entry->creators as $creator)
		{
			$this->addToGroup($creator, $entry);
		}
	}
}
