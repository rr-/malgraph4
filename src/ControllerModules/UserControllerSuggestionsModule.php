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

	public static function work(&$viewContext)
	{
		$viewContext->viewName = 'user-suggestions';
		$viewContext->meta->title = 'MALgraph - ' . $viewContext->user->name . ' - suggestions (' . Media::toString($viewContext->media) . ')';
		$viewContext->meta->description = $viewContext->user->name . '&rsquo;s ' . Media::toString($viewContext->media) . ' suggestions on MALgraph, an online tool that extends your MyAnimeList profile.';
		$viewContext->meta->keywords = array_merge($viewContext->meta->keywords, ['profile', 'list', 'achievements', 'ratings', 'activity', 'favorites', 'suggestions', 'recommendations']);
		WebMediaHelper::addCustom($viewContext);


		//get list of cool users
		$goal = 50;
		$mainUser = $viewContext->user;
		$coolUsers = Model_User::getCoolUsers($goal);
		$coolUsers = array_filter($coolUsers, function($user) use ($mainUser) { return $user->id != $mainUser->id; });

		//get their stuff, like lists and mean scores
		$lists = [];
		$meanScores = [];
		foreach (array_merge($coolUsers, [$mainUser]) as $user)
		{
			$list = $user->getMixedUserMedia($viewContext->media);
			$listCompleted = UserMediaFilter::doFilter($list, UserMediaFilter::finished());
			$keys = array_map(function($e) { return $e->media . $e->mal_id; }, $listCompleted);
			$values = $listCompleted;
			$lists[$user->id] = array_combine($keys, $values);
			$dist = RatingDistribution::fromEntries($lists[$user->id]);
			$meanScores[$user->id] = $dist->getMeanScore();
		}
		$addStatic = count($lists[$mainUser->id]) <= 20;

		//fill base entries
		$selectedEntries = [];
		foreach ($coolUsers as $coolUser)
		{
			//compute similarity indexes between "me" and selected user
			$sum1 = $sum2a = $sum2b = 0;
			foreach ($lists[$mainUser->id] as $e1)
			{
				$key = $e1->media . $e1->mal_id;
				if (isset($lists[$coolUser->id][$key]))
				{
					$e2 = $lists[$coolUser->id][$key];
					$tmp1 = ($e1->score - $meanScores[$mainUser->id]);
					$tmp2 = ($e2->score - $meanScores[$coolUser->id]);
					$sum1 += $tmp1 * $tmp2;
					$sum2a += $tmp1 * $tmp1;
					$sum2b += $tmp2 * $tmp2;
				}
			}
			$similarity = $sum1 / max(1, sqrt($sum2a * $sum2b));

			//check what titles are on their list
			foreach ($lists[$coolUser->id] as $e2)
			{
				$add = false;
				$key = $e2->media . $e2->mal_id;
				if (!isset($lists[$mainUser->id][$key]))
				{
					$add = true;
				}
				else
				{
					$e1 = $lists[$mainUser->id][$key];
					if ($e1->status == UserListStatus::Planned)
					{
						$add = true;
					}
				}
				if ($add)
				{
					if (!isset($selectedEntries[$key]))
					{
						$e2->cfScore = $meanScores[$mainUser->id];
						$selectedEntries[$key] = $e2;
					}
					$selectedEntries[$key]->cfScore += $similarity * ($e2->score - $meanScores[$coolUser->id]);
				}
			}
		}

		//sort these entries by rating
		uasort($selectedEntries, function($a, $b)
		{
			return $a->cfScore < $b->cfScore ? 1 : -1;
		});

		//append shuffled static recommendations at the end of above recommendations
		$staticRecIds = TextHelper::loadSimpleList(Config::$staticRecommendationListPath);
		$staticRecEntries = Model_MixedUserMedia::getFromIdList($staticRecIds);
		shuffle($staticRecEntries);
		foreach ($staticRecEntries as $entry)
		{
			$entry->cfScore = $entry->average_score;
			$selectedEntries []= $entry;
		}

		//trim entries to 25 entries
		//this is to reduce franchise computation time. note that we add extra
		//10 entries so that franchises that will be completely filtered out
		//due to whatever reason (for example, everything still not watched
		//hasn't aired yet) won't reduce suggestion count to under desired 15.
		$selectedEntries = array_slice($selectedEntries, 0, 25);

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
			$cfScore = reset($franchise->ownEntries)->cfScore;
			foreach ($franchise->allEntries as $entry)
			{
				if ($entry->publishing_status == MediaStatus::NotYetPublished)
				{
					continue;
				}
				$key = $entry->media . $entry->mal_id;
				if (isset($lists[$mainUser->id][$key]))
				{
					continue;
				}
				$finalEntry = reset($franchise->allEntries);
				if (!isset($finalEntry->cfScore))
				{
					$finalEntry->cfScore = $cfScore;
				}
				$finalEntries []= $finalEntry;
				break;
			}
		}
		$finalEntries = array_slice($finalEntries, 0, 15);
		$viewContext->newRecommendations = $finalEntries;




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
