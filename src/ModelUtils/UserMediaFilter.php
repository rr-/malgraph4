<?php
class UserMediaFilter
{
	public static function doFilter($entries, $filters)
	{
		if (empty($filters))
		{
			return;
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

	public static function genre($genreId, $list)
	{
		R::begin();
		R::exec('CREATE TEMPORARY TABLE hurr (media_id INTEGER)');
		foreach (array_chunk(array_map(function($entry) { return $entry->media_id; }, $list), Config::$maxDbBindings) as $chunk)
		{
			R::exec('INSERT INTO hurr VALUES ' . join(',', array_fill(0, count($chunk), '(?)')), $chunk);
		}
		$data = R::getAll('SELECT * FROM mediagenre mg INNER JOIN hurr ON mg.media_id = hurr.media_id WHERE mg.mal_id = ?', [$genreId]);
		R::rollback();

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
