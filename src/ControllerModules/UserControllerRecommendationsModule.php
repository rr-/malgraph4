<?php
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
			'sug', 'sugg', 'sugs', 'suggestions',
			'rec', 'recs', 'recommended', 'recommendations'
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


	private static function addRecsFromRecommendations($viewContext, $goal, $list, array &$selectedEntries, array $dontRecommend)
	{
		Model_MixedUserMedia::attachRecommendations($list);

		$dist = RatingDistribution::fromEntries($list);
		$meanScore = $dist->getMeanScore();

		$selectedEntriesWeights = [];
		foreach ($list as $entry)
		{
			$key1 = $entry->media . $entry->mal_id;
			foreach ($entry->recommendations as $rec)
			{
				$key2 = $entry->media . $rec->mal_id;
				if (!isset($selectedEntriesWeights[$key2]))
				{
					$selectedEntriesWeights[$key2] = [];
				}
				$selectedEntriesWeights[$key2] []= [$entry, $rec->count];
			}
		}

		foreach ($selectedEntriesWeights as $key => $items)
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
			$selectedEntriesWeights[$key] =
				(1 - $weight) * ($sum / max(1, $count)) +
				$weight * $meanScore;
		}

		foreach (array_keys($dontRecommend) as $key)
		{
			unset($selectedEntriesWeights[$key]);
		}

		//make it faster by trimming the list
		arsort($selectedEntriesWeights, SORT_NUMERIC);
		$selectedEntriesWeights = array_slice($selectedEntriesWeights, 0, $goal * 10);

		foreach (Model_MixedUserMedia::getFromIdList(array_keys($selectedEntriesWeights)) as $entry)
		{
			$key = $entry->media . $entry->mal_id;
			$entry->hypothetical_score = $selectedEntriesWeights[$key];
			if (!isset($selectedEntries[$key]))
			{
				$selectedEntries[$key] = $entry;
			}
		}
	}


	private static function addRecsFromStaticRecommendations($viewContext, $goal, array $dontRecommend, array &$selectedEntries)
	{
		$staticRecIds = TextHelper::loadSimpleList(Config::$staticRecommendationListPath);
		$staticRecEntries = Model_MixedUserMedia::getFromIdList($staticRecIds);
		foreach ($staticRecEntries as $entry)
		{
			if ($entry->media != $viewContext->media)
			{
				continue;
			}
			$key = $entry->media . $entry->mal_id;
			if (isset($dontRecommend[$key]))
			{
				continue;
			}
			if (!isset($selectedEntries[$key]))
			{
				#$entry->hypothetical_score = $entry->average_score;
				$entry->hypothetical_score = mt_rand() % 10;
				$selectedEntries[$key] = $entry;
			}
		}
	}


	private static function filterBannedGenres($viewContext, $goal, array &$selectedEntries)
	{
		//make it faster by trimming the list
		uasort($selectedEntries, function($a, $b)
		{
			return $a->hypothetical_score < $b->hypothetical_score ? 1 : -1;
		});
		$selectedEntries = array_slice($selectedEntries, 0, $goal * 10);

		Model_MixedUserMedia::attachGenres($selectedEntries);
		$finalEntries = [];
		foreach ($selectedEntries as $entry)
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
				$finalEntries []= $entry;
			}
		}
		$selectedEntries = $finalEntries;
	}


	private static function filterFranchises($viewContext, $goal, $list, array &$selectedEntries)
	{
		$franchises = Model_MixedUserMedia::getFranchises($selectedEntries, true);
		$selectedEntries = [];
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
				$selectedEntries[$key] = $entryToAdd;
			}
		}
	}


	private static function getRecs($viewContext, $goal, $list, $allFranchises)
	{
		$dontRecommend = [];
		foreach ($allFranchises as $franchise)
		{
			foreach ($franchise->allEntries as $entry)
			{
				$key = $entry->media . $entry->mal_id;
				$dontRecommend[$key] = true;
			}
		}

		$selectedEntries = [];
		self::addRecsFromRecommendations($viewContext, $goal, $list, $selectedEntries, $dontRecommend);
		self::addRecsFromStaticRecommendations($viewContext, $goal, $dontRecommend, $selectedEntries);
		self::filterBannedGenres($viewContext, $goal, $selectedEntries);
		self::filterFranchises($viewContext, $goal, $dontRecommend, $selectedEntries);

		uasort($selectedEntries, function($a, $b)
		{
			return $a->hypothetical_score < $b->hypothetical_score ? 1 : -1;
		});
		$selectedEntries = array_slice($selectedEntries, 0, $goal);

		foreach ($selectedEntries as $entry)
		{
			$entry->media_id = $entry->id;
		}
		Model_MixedUserMedia::attachGenres($selectedEntries);

		return $selectedEntries;
	}


	private static function getMissing($viewContext, $list, $allFranchises)
	{
		$dontRecommend = [];
		foreach ($list as $entry)
		{
			$dontRecommend[$entry->media . $entry->mal_id] = true;
		}

		$franchises = [];
		foreach ($allFranchises as &$franchise)
		{
			$franchise->allEntries = array_filter($franchise->allEntries,
				function ($entry) use ($viewContext, $dontRecommend)
				{
					if ($entry->media != $viewContext->media)
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


	public static function work(&$viewContext)
	{
		$viewContext->viewName = 'user-recommendations';
		$viewContext->meta->title = 'MALgraph - ' . $viewContext->user->name . ' - recommendations (' . Media::toString($viewContext->media) . ')';
		$viewContext->meta->description = $viewContext->user->name . '&rsquo;s ' . Media::toString($viewContext->media) . ' recommendations on MALgraph, an online tool that extends your MyAnimeList profile.';
		$viewContext->meta->keywords = array_merge($viewContext->meta->keywords, ['profile', 'list', 'achievements', 'ratings', 'activity', 'favorites', 'recommendations']);
		WebMediaHelper::addCustom($viewContext);


		$list = [];
		foreach ($viewContext->user->getMixedUserMedia($viewContext->media) as $entry)
		{
			$key = $entry->media . $entry->mal_id;
			$list[$key] = $entry;
		}
		$allFranchises = Model_MixedUserMedia::getFranchises($list, true);

		$goal = 10;
		$viewContext->newRecommendations = self::getRecs($viewContext, $goal, $list, $allFranchises);
		$viewContext->franchises = self::getMissing($viewContext, $list, $allFranchises);
		$viewContext->private = $viewContext->user->isUserMediaPrivate($viewContext->media);
	}
}
