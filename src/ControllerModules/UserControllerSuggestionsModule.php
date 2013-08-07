<?php
class UserControllerSuggestionsModule extends AbstractUserControllerModule
{
	public static function getText(ViewContext $viewContext, $media)
	{
		return 'Suggestions';
	}

	public static function getUrlParts()
	{
		return ['sug', 'sugg', 'suggestions', 'rec', 'recs', 'recommendations'];
	}

	public static function getMediaAvailability()
	{
		return [Media::Anime, Media::Manga];
	}

	public static function getOrder()
	{
		return 5;
	}


	private static function addRecsFromCollaborativeFiltering($viewContext, $goal, $list, array &$selectedEntries)
	{
		//get list of cool users
		$mainUser = $viewContext->user;
		$coolUsers = Model_User::getCoolUsers(20);
		$coolUsers = array_filter($coolUsers, function($user) use ($mainUser) { return $user->id != $mainUser->id; });

		//get their stuff, like lists and mean scores
		$lists = [];
		$listsCompleted = [];
		$meanScores = [];
		foreach (array_merge($coolUsers, [$mainUser]) as $user)
		{
			$list = $user->getMixedUserMedia($viewContext->media);
			$keys = array_map(function($e) { return $e->media . $e->mal_id; }, $list);
			$lists[$user->id] = array_combine($keys, $list);

			$listCompleted = UserMediaFilter::doFilter($list, UserMediaFilter::finished());
			$keys = array_map(function($e) { return $e->media . $e->mal_id; }, $listCompleted);
			$listsCompleted[$user->id] = array_combine($keys, $listCompleted);

			$dist = RatingDistribution::fromEntries($listsCompleted[$user->id]);
			$meanScores[$user->id] = $dist->getMeanScore();
		}

		//fill base entries
		$selectedEntries = [];
		foreach ($coolUsers as $coolUser)
		{
			//compute similarity indexes between "me" and selected user
			$sum1 = $sum2a = $sum2b = 0;
			foreach ($listsCompleted[$mainUser->id] as $e1)
			{
				$key = $e1->media . $e1->mal_id;
				if (isset($listsCompleted[$coolUser->id][$key]))
				{
					$e2 = $listsCompleted[$coolUser->id][$key];
					$tmp1 = ($e1->score - $meanScores[$mainUser->id]);
					$tmp2 = ($e2->score - $meanScores[$coolUser->id]);
					$sum1 += $tmp1 * $tmp2;
					$sum2a += $tmp1 * $tmp1;
					$sum2b += $tmp2 * $tmp2;
				}
			}
			$similarity = $sum1 / max(1, sqrt($sum2a * $sum2b));

			//check what titles are on their list
			foreach ($listsCompleted[$coolUser->id] as $e2)
			{
				$key = $e2->media . $e2->mal_id;
				if (!isset($lists[$mainUser->id][$key]))
				{
					if (!isset($selectedEntries[$key]))
					{
						$e2->hypothetical_score = 0;
						$e2->cf_normalize = 0;
						$selectedEntries[$key] = $e2;
					}
					$selectedEntries[$key]->hypothetical_score += $similarity * ($e2->score - $meanScores[$coolUser->id]);
					$selectedEntries[$key]->cf_normalize += abs($similarity);
				}
			}
		}
		foreach ($selectedEntries as $key => $e)
		{
			$e->hypothetical_score /= max(1, $e->cf_normalize);
			$e->hypothetical_score += $meanScores[$mainUser->id];
		}
	}


	private static function addRecsFromStaticRecommendations($viewContext, $goal, $list, array &$selectedEntries)
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
			if (isset($list[$key]))
			{
				continue;
			}
			if (!isset($selectedEntries[$key]))
			{
				$entry->hypothetical_score = $entry->average_score;
				$selectedEntries[$key] = $entry;
			}
		}
	}


	private static function filterBannedGenres($viewContext, $goal, $list, array &$selectedEntries)
	{
		//make it faster by trimming the list
		uasort($selectedEntries, function($a, $b)
		{
			return $a->hypothetical_score < $b->hypothetical_score ? 1 : -1;
		});
		$selectedEntries = array_slice($selectedEntries, 0, $goal * 3);

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
				if (isset($list[$key]))
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


	private static function getRecs($viewContext, $goal)
	{
		$list = [];
		foreach ($viewContext->user->getMixedUserMedia($viewContext->media) as $entry)
		{
			$key = $entry->media . $entry->mal_id;
			$list[$key] = $entry;
		}

		$selectedEntries = [];
		self::addRecsFromCollaborativeFiltering($viewContext, $goal, $list, $selectedEntries);
		self::addRecsFromStaticRecommendations($viewContext, $goal, $list, $selectedEntries);
		self::filterBannedGenres($viewContext, $goal, $list, $selectedEntries);
		self::filterFranchises($viewContext, $goal, $list, $selectedEntries);

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



	public static function work(&$viewContext)
	{
		$viewContext->viewName = 'user-suggestions';
		$viewContext->meta->title = 'MALgraph - ' . $viewContext->user->name . ' - suggestions (' . Media::toString($viewContext->media) . ')';
		$viewContext->meta->description = $viewContext->user->name . '&rsquo;s ' . Media::toString($viewContext->media) . ' suggestions on MALgraph, an online tool that extends your MyAnimeList profile.';
		$viewContext->meta->keywords = array_merge($viewContext->meta->keywords, ['profile', 'list', 'achievements', 'ratings', 'activity', 'favorites', 'suggestions', 'recommendations']);
		WebMediaHelper::addCustom($viewContext);


		$goal = 10;
		$viewContext->newRecommendations = self::getRecs($viewContext, $goal);


		$list = $viewContext->user->getMixedUserMedia($viewContext->media);
		$dontRecommend = [];
		foreach ($list as $entry)
		{
			$dontRecommend[$entry->media . $entry->mal_id] = true;
		}

		$allFranchises = Model_MixedUserMedia::getFranchises($list, true);
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

		$viewContext->franchises = $franchises;
		$viewContext->private = $viewContext->user->isUserMediaPrivate($viewContext->media);
	}
}
