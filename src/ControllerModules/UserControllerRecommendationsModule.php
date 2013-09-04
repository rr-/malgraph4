<?php
class RecommendationsEngine
{
	private $media;
	private $list;
	private $allFranchises;

	public function __construct($media, $list)
	{
		$this->media = $media;

		$keyedList = [];
		foreach ($list as $entry)
		{
			$key = $entry->media . $entry->mal_id;
			$keyedList[$key] = $entry;
		}
		$this->allFranchises = Model_MixedUserMedia::getFranchises($keyedList, true);
		$this->list = $keyedList;
	}



	private static function addRecsFromRecommendations($list)
	{
		Model_MixedUserMedia::attachRecommendations($list);

		$count = 0;
		$meanScore = 0;
		foreach ($list as $entry)
		{
			$meanScore += $entry->score;
			$count += $entry->score > 0;
		}
		$meanScore /= max(1, $count);

		$weights = [];
		foreach ($list as $entry)
		{
			$key1 = $entry->media . $entry->mal_id;
			foreach ($entry->recommendations as $rec)
			{
				$key2 = $entry->media . $rec->mal_id;
				if (!isset($weights[$key2]))
				{
					$weights[$key2] = [];
				}
				$weights[$key2] []= [$entry, $rec->count];
			}
		}

		foreach ($weights as $key => $items)
		{
			$maxWeight = max(array_map(function($item) { return $item[1]; }, $items));
			$sum = 0;
			$count = 0;
			foreach ($items as $item)
			{
				list ($sourceEntry, $weight) = $item;
				$sum += ($sourceEntry->score ?: $sourceEntry->average_score) * $weight;
				$count += $weight;
			}
			#the more recommendations, the more close it will be to source scores (log scale)
			$const = 1.2;
			$weight = pow($const, - $count);
			$weights[$key] =
				(1 - $weight) * ($sum / max(1, $count)) +
				$weight * $meanScore;
		}

		return $weights;
	}


	private static function addRecsFromStaticRecommendations($media)
	{
		$staticRecIds = TextHelper::loadSimpleList(Config::$staticRecommendationListPath);
		$weights = [];
		foreach ($staticRecIds as $id)
		{
			$recMedia = substr($id, 0, 1);
			if ($recMedia == $media)
			{
				$weights[$id] = null;
			}
		}
		return $weights;
	}


	private static function getRecsFromWeights($weights)
	{
		$entries = Model_MixedUserMedia::getFromIdList(array_keys($weights));
		$finalEntries = [];
		foreach ($entries as $entry)
		{
			$key = $entry->media . $entry->mal_id;
			$entry->hypothetical_score = $weights[$key] ?: $entry->average_score;
			$finalEntries[$key] = $entry;
		}
		return $finalEntries;
	}


	private static function trimByValue($input, $goal)
	{
		arsort($input, SORT_NUMERIC);
		return array_slice($input, 0, $goal);
	}


	private static function trimByScore($input, $goal)
	{
		uasort($input, function($a, $b)
		{
			return $a->hypothetical_score <= $b->hypothetical_score ? 1 : -1;
		});
		return array_slice($input, 0, $goal);
	}


	private static function filterKeys($input, $filteredKeys)
	{
		$output = [];
		foreach ($input as $key => $value)
		{
			if (!isset($filteredKeys[$key]))
			{
				$output[$key] = $value;
			}
		}
		return $output;
	}


	private static function filterBannedGenres($selectedEntries)
	{
		Model_MixedUserMedia::attachGenres($selectedEntries);
		$finalEntries = [];
		foreach ($selectedEntries as $key => $entry)
		{
			$add = true;
			foreach ($entry->genres as $genre)
			{
				if (BanHelper::isGenreBannedForRecs($entry->media, $genre->mal_id))
				{
					$add = false;
					break;
				}
			}
			if ($add)
			{
				$finalEntries[$key] = $entry;
			}
		}
		return $finalEntries;
	}


	private static function filterFranchises($selectedEntries)
	{
		$franchises = Model_MixedUserMedia::getFranchises($selectedEntries, true);
		$finalEntries = [];
		foreach ($franchises as $franchise)
		{
			DataSorter::sort($franchise->allEntries, DataSorter::MediaMalId);
			DataSorter::sort($franchise->ownEntries, DataSorter::MediaMalId);

			$entryToAdd = null;
			$franchiseSize = 0;
			foreach ($franchise->allEntries as $entry)
			{
				if ($entry->publishing_status == MediaStatus::NotYetPublished)
				{
					continue;
				}

				if ($entry->media == Media::Anime)
				{
					$franchiseSize += $entry->episodes;
				}
				elseif ($entry->media == Media::Manga)
				{
					$franchiseSize += $entry->chapters;
				}

				$key = $entry->media . $entry->mal_id;
				if (isset($dontRecommend[$key]))
				{
					$entryToAdd = null;
					break;
				}
				if ($entryToAdd === null)
				{
					$entryToAdd = $entry;
				}
			}

			if ($entryToAdd !== null)
			{
				$entryToAdd->franchiseSize = $franchiseSize;
				if (!isset($entryToAdd->hypothetical_score))
				{
					$entryToAdd->hypothetical_score = reset($franchise->ownEntries)->hypothetical_score;
				}
				$finalEntries[$key] = $entryToAdd;
			}
		}
		return $finalEntries;
	}


	public function getNewRecommendations($goal)
	{
		$dontRecommend = [];
		foreach ($this->allFranchises as $franchise)
		{
			foreach ($franchise->allEntries as $entry)
			{
				$key = $entry->media . $entry->mal_id;
				$dontRecommend[$key] = true;
			}
		}

		$selectedEntries = [];

		$weights1 = self::addRecsFromRecommendations($this->list);
		$weights1 = self::filterKeys($weights1, $dontRecommend);
		$weights1 = self::trimByValue($weights1, $goal * 10);

		$weights2 = self::addRecsFromStaticRecommendations($this->media);
		$weights2 = self::filterKeys($weights2, $dontRecommend);

		$allWeights = array_merge($weights2, $weights1);

		$selectedEntries = self::getRecsFromWeights($allWeights);

		$selectedEntries = self::trimByScore($selectedEntries, $goal * 10);
		$selectedEntries = self::filterBannedGenres($selectedEntries);

		$selectedEntries = self::filterFranchises($selectedEntries);

		$selectedEntries = self::trimByScore($selectedEntries, $goal);

		//reattach stuff that might have been lost due to whatever reason
		foreach ($selectedEntries as $entry)
		{
			$entry->media_id = $entry->id;
		}
		Model_MixedUserMedia::attachGenres($selectedEntries);

		return $selectedEntries;
	}



	public function getMissingTitles()
	{
		$dontRecommend = [];
		foreach ($this->list as $entry)
		{
			$dontRecommend[$entry->media . $entry->mal_id] = true;
		}

		$franchises = [];
		foreach ($this->allFranchises as &$franchise)
		{
			$franchise->allEntries = array_filter($franchise->allEntries,
				function ($entry) use ($dontRecommend)
				{
					if ($entry->media != $this->media)
					{
						return false;
					}
					if (isset($dontRecommend[$entry->media . $entry->mal_id]))
					{
						return false;
					}
					return true;
				});
			if (empty($franchise->allEntries))
			{
				continue;
			}

			DataSorter::sort($franchise->allEntries, DataSorter::MediaMalId);
			DataSorter::sort($franchise->ownEntries, DataSorter::MediaMalId);
			$dist = RatingDistribution::fromEntries($franchise->ownEntries);
			$franchise->meanScore = $dist->getMeanScore();
			$franchises []= $franchise;
		}
		DataSorter::sort($franchises, DataSorter::MeanScore);
		return $franchises;
	}
}



class UserControllerRecommendationsModule extends AbstractUserControllerModule
{
	public static function getText(ViewContext $viewContext, $media)
	{
		return 'Recommended';
	}

	public static function getUrlParts()
	{
		return
		[
			'recs', 'rec', 'recommended', 'recommendations',
			'sugs', 'sug', 'sugg', 'suggestions'
		];
	}

	public static function getMediaAvailability()
	{
		return [Media::Anime, Media::Manga];
	}

	public static function getOrder()
	{
		return 5;
	}



	public static function work(&$controllerContext, &$viewContext)
	{
		$viewContext->viewName = 'user-recommendations';
		$viewContext->meta->title = 'MALgraph - ' . $viewContext->user->name . ' - recommendations (' . Media::toString($viewContext->media) . ')';
		$viewContext->meta->description = $viewContext->user->name . '&rsquo;s ' . Media::toString($viewContext->media) . ' recommendations on MALgraph, an online tool that extends your MyAnimeList profile.';
		$viewContext->meta->keywords = array_merge($viewContext->meta->keywords, ['profile', 'list', 'achievements', 'ratings', 'history', 'favorites', 'recommendations']);
		WebMediaHelper::addCustom($viewContext);

		$list = $viewContext->user->getMixedUserMedia($viewContext->media);
		$recsEngine = new RecommendationsEngine($viewContext->media, $list);

		$goal = 10;
		$viewContext->newRecommendations = $recsEngine->getNewRecommendations($goal);
		$viewContext->franchises = $recsEngine->getMissingTitles();
		$viewContext->private = $viewContext->user->isUserMediaPrivate($viewContext->media);
	}
}
