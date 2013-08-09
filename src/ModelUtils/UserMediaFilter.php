<?php
class UserMediaFilter
{
	public static function doFilter($entries, $filters)
	{
		if (empty($filters))
		{
			return $entries;
		}
		foreach ((array) $filters as $filter)
		{
			$entries = array_filter($entries, $filter);
		}
		return $entries;
	}

	public static function nonPlanned()
	{
		return function($row)
		{
			return $row->status != UserListStatus::Planned;
		};
	}

	public static function dropped()
	{
		return function($row)
		{
			return $row->status == UserListStatus::Dropped;
		};
	}

	public static function finished()
	{
		return function($row)
		{
			return $row->status == UserListStatus::Finished;
		};
	}

	public static function score($score)
	{
		$score = intval($score);
		return function($row) use ($score)
		{
			return intval($row->score) == $score;
		};
	}

	public static function combine()
	{
		return func_get_args();
	}

	public static function lengthGroup($group)
	{
		return function($row) use ($group)
		{
			return MediaLengthDistribution::getGroup($row) == $group;
		};
	}

	public static function publishedYear($year)
	{
		return function($row) use ($year)
		{
			return MediaYearDistribution::getPublishedYear($row) == $year;
		};
	}

	public static function publishedDecade($decade)
	{
		return function($row) use ($decade)
		{
			return MediaDecadeDistribution::getPublishedDecade($row) == $decade;
		};
	}

	public static function nonMovie()
	{
		return function($row)
		{
			return !($row->sub_type == AnimeMediaType::Movie and $row->media == Media::Anime);
		};
	}

	public static function creator($genreId, $list)
	{
		if (empty($list))
		{
			return [];
		}
		$media = reset($list)->media;
		$query = 'CREATE TEMPORARY TABLE hurr (media_id INTEGER)';
		R::exec($query);
		foreach (array_chunk(array_map(function($entry) { return $entry->media_id; }, $list), Config::$maxDbBindings) as $chunk)
		{
			$query = 'INSERT INTO hurr VALUES ' . join(',', array_fill(0, count($chunk), '(?)'));
			R::exec($query, $chunk);
		}
		switch ($media)
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
		$query = 'SELECT * FROM ' . $table . ' mc INNER JOIN hurr ON mc.media_id = hurr.media_id WHERE mc.mal_id = ?';
		$data = R::getAll($query, [$genreId]);
		$query = 'DROP TABLE hurr';
		R::exec($query);

		$data = array_map(function($x) { return $x['media_id']; }, $data);
		$data = array_flip($data);
		return function($row) use ($data)
		{
			return isset($data[$row->media_id]);
		};
	}


	public static function genre($genreId, $list)
	{
		$query = 'CREATE TEMPORARY TABLE hurr (media_id INTEGER)';
		R::exec($query);
		foreach (array_chunk(array_map(function($entry) { return $entry->media_id; }, $list), Config::$maxDbBindings) as $chunk)
		{
			$query = 'INSERT INTO hurr VALUES ' . join(',', array_fill(0, count($chunk), '(?)'));
			R::exec($query, $chunk);
		}
		$query = 'SELECT * FROM mediagenre mg INNER JOIN hurr ON mg.media_id = hurr.media_id WHERE mg.mal_id = ?';
		$data = R::getAll($query, [$genreId]);
		$query = 'DROP TABLE hurr';
		R::exec($query);

		$data = array_map(function($x) { return $x['media_id']; }, $data);
		$data = array_flip($data);
		return function($row) use ($data)
		{
			return isset($data[$row->media_id]);
		};
	}

	public static function givenMedia($mediaList)
	{
		return function($e) use ($mediaList)
		{
			return in_array($e->media . $e->mal_id, $mediaList);
		};
	}
}
