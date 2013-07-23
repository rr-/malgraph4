<?php
class UserControllerEntriesModule extends AbstractUserControllerModule
{
	public static function getUrlParts()
	{
		return ['entries'];
	}

	public static function getMediaAvailability()
	{
		return [];
	}

	public static function work(&$viewContext)
	{
		$sender = $_GET['sender'];
		$filterParam = isset($_GET['filter-param']) ? $_GET['filter-param'] : null;
		if (isset($_GET['media']) and in_array($_GET['media'], Media::getConstList()))
		{
			$viewContext->media = $_GET['media'];
		}

		$viewContext->viewName = 'user-entries-' . $sender;
		$viewContext->layoutName = 'layout-ajax';
		$viewContext->filterParam = $filterParam;
		$list = $viewContext->user->getMixedUserMedia($viewContext->media);

		$computeMeanScore = null;
		switch ($sender)
		{
			case 'ratings':
				$cb = function($row) use ($filterParam)
				{
					return intval($row->score) == intval($filterParam)
						and $row->status != UserListStatus::Planned;
				};
				break;
			case 'length':
				$cb = function($row) use ($filterParam)
				{
					return MediaLengthDistribution::getGroup($row) == $filterParam
						and $row->status != UserListStatus::Planned
						and !($row->sub_type == AnimeMediaType::Movie and $row->media == Media::Anime);
				};
				$computeMeanScore = true;
				break;
			case 'year':
				$cb = function($row) use ($filterParam)
				{
					return $row->status != UserListStatus::Planned
						and MediaYearDistribution::getPublishedYear($row) == $filterParam;
				};
				$computeMeanScore = true;
				break;
			case 'decade':
				$cb = function($row) use ($filterParam)
				{
					return $row->status != UserListStatus::Planned
						and MediaDecadeDistribution::getPublishedDecade($row) == $filterParam;
				};
				$computeMeanScore = true;
				break;
			case 'genre':
				R::begin();
				R::exec('CREATE TEMPORARY TABLE hurr (media_id INTEGER)');
				$listNonPlanned = array_filter($list, function($a) { return $a->status != UserListStatus::Planned; });
				foreach (array_chunk(array_map(function($entry) { return $entry->media_id; }, $listNonPlanned), Config::$maxDbBindings) as $chunk)
				{
					R::exec('INSERT INTO hurr VALUES ' . join(',', array_fill(0, count($chunk), '(?)')), $chunk);
				}
				$data = R::getAll('SELECT * FROM mediagenre mg INNER JOIN hurr ON mg.media_id = hurr.media_id WHERE mg.mal_id = ?', [$viewContext->filterParam]);
				R::rollback();
				$viewContext->genreName = count($data) ? $data[0]['name'] : null;
				$data = array_map(function($x) { return $x['media_id']; }, $data);
				$data = array_flip($data);
				$cb = function($row) use ($data)
				{
					return isset($data[$row->media_id]);
				};
				$computeMeanScore = true;
				break;
			case 'franchises':
				$cb = function($row)
				{
					return $row->status != UserListStatus::Planned;
				};
				break;
			case 'mismatches':
				$cb = function($row)
				{
					return true;
				};
				break;
			default:
				throw new Exception('Unknown sender (' . $sender . ')');
		}

		$list = array_filter($list, $cb);
		$isPrivate = $viewContext->user->isUserMediaPrivate($viewContext->media);

		if (!$isPrivate)
		{
			if ($computeMeanScore)
			{
				$dist = RatingDistribution::fromEntries($list);
				$viewContext->meanScore = $dist->getMeanScore();
			}
			if ($sender == 'franchises')
			{
				$franchises = $viewContext->user->getFranchisesFromUserMedia($list);
				foreach ($franchises as &$franchise)
				{
					$dist = RatingDistribution::fromEntries($franchise->ownEntries);
					$franchise->meanScore = $dist->getMeanScore();
				}
				unset($franchise);
				DataSorter::sort($entries, DataSorter::MeanScore);
				$viewContext->franchises = array_filter($franchises, function($franchise) { return count($franchise->ownEntries) > 1; });
			}
			elseif ($sender == 'mismatches')
			{
				$entries = $viewContext->user->getMismatchedUserMedia($list);
				DataSorter::sort($entries, DataSorter::Title);
				$viewContext->entries = $entries;
			}
			else
			{
				DataSorter::sort($list, DataSorter::Title);
				$viewContext->entries = $list;
			}
		}

		$viewContext->isPrivate = $isPrivate;
	}
}
