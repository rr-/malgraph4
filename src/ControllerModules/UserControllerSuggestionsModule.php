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

	private static function getRecs($viewContext, $goal)
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
		$addStatic = count($listsCompleted[$mainUser->id]) <= 20;

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
						$e2->cfScore = 0;
						$e2->cfNormalize = 0;
						$e2->cfUsers = 0;
						$selectedEntries[$key] = $e2;
					}
					$selectedEntries[$key]->cfScore += $similarity * ($e2->score - $meanScores[$coolUser->id]);
					$selectedEntries[$key]->cfUsers ++;
					$selectedEntries[$key]->cfNormalize += abs($similarity);
				}
			}
		}
		foreach ($selectedEntries as $key => $e)
		{
			$e->cfScore /= max(1, $e->cfNormalize);
			$e->cfScore += $meanScores[$mainUser->id];
		}

		//sort these entries by rating
		uasort($selectedEntries, function($a, $b)
		{
			return $a->cfScore < $b->cfScore ? 1 : -1;
		});

		//append shuffled static recommendations at the end of above
		//recommendations
		$staticRecIds = TextHelper::loadSimpleList(Config::$staticRecommendationListPath);
		$staticRecEntries = Model_MixedUserMedia::getFromIdList($staticRecIds);
		foreach ($staticRecEntries as $entry)
		{
			if ($entry->media != $viewContext->media)
			{
				continue;
			}
			$entry->cfScore = $entry->average_score;
			$entry->cfUsers = 0;
			$key = $entry->media . $entry->mal_id;
			if (isset($lists[$mainUser->id][$key]))
			{
				continue;
			}
			$key = $entry->media . $entry->mal_id;
			if (!isset($selectedEntries[$key]))
			{
				$selectedEntries[$key] = $entry;
			}
		}

		//trim entries to 25 entries
		//this is to reduce franchise computation time. note that we add extra
		//10 entries so that franchises that will be completely filtered out
		//due to whatever reason (for example, everything still not watched
		//hasn't aired yet) won't reduce suggestion count to under desired 15.
		$selectedEntries = array_slice($selectedEntries, 0, $goal * 3);

		//finally for each recommended entry, get first non-watched entry from
		//franchise that is already airing. this is to prevent recommending
		//season 15 when user has only watched season 3 and properly
		//recommending season 4 instead.
		$franchises = Model_MixedUserMedia::getFranchises($selectedEntries, true);
		$finalEntries = [];
		foreach ($franchises as $franchise)
		{
			DataSorter::sort($franchise->allEntries, DataSorter::MediaMalId);
			DataSorter::sort($franchise->ownEntries, DataSorter::MediaMalId);
			$franchiseSize = 0;
			foreach ($franchise->allEntries as $entry)
			{
				if ($entry->media == Media::Anime)
				{
					$franchiseSize += $entry->episodes;
				}
				elseif ($entry->media == Media::Manga)
				{
					$franchiseSize += $entry->chapters;
				}
			}
			foreach ($franchise->allEntries as $entry)
			{
				if ($entry->publishing_status == MediaStatus::NotYetPublished)
				{
					continue;
				}
				$key = $entry->media . $entry->mal_id;
				if (isset($lists[$mainUser->id][$key]))
				{
					break;
				}
				$entry->franchiseSize = $franchiseSize;
				if (!isset($entry->cfScore))
				{
					$entry->cfScore = reset($franchise->ownEntries)->cfScore;
					$entry->cfUsers = reset($franchise->ownEntries)->cfUsers;
				}
				$finalEntries[$key] = $entry;
				break;
			}
		}
		$selectedEntries = $finalEntries;
		$selectedEntries = array_slice($selectedEntries, 0, $goal);

		//sort these entries by rating again, score could have changed after
		//franchise tinkering
		uasort($selectedEntries, function($a, $b)
		{
			return $a->cfScore < $b->cfScore ? 1 : -1;
		});

		foreach ($selectedEntries as $entry)
		{
			$entry->hypotheticalScore = $entry->cfScore;
			$entry->media_id = $entry->id;
		}
		MediaGenreDistribution::attachGenres($selectedEntries);

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
