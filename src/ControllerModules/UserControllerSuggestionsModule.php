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


		$goal = 50;
		$mainUser = $viewContext->user;
		$coolUsers = Model_User::getCoolUsers($goal);
		#$coolUsers = array_filter($coolUsers, function($user) use ($mainUser) { return $user->id != $mainUser->id; });
		var_dump($coolUsers);die;

		$lists = [];
		$meanScores = [];
		foreach (array_merge($coolUsers, (array) $mainUser) as $user)
		{
			$list = $user->getMixedUserMedia($viewContext->media);
			$listCompleted = UserMediaFilter::doFilter($list, UserMediaFilter::finished());
			$keys = array_map(function($e) { return $e->id; }, $listCompleted);
			$values = $listCompleted;
			$lists[$user->id] = array_combine($keys, $values);
			$meanScores[$user->id] = $dist->getMeanScore();
		}
		$addStatic = count($lists[$mainUser->id]) <= 20;

		#$simNormalize = 0;
		$selectedEntries = [];
		$similarity = [];
		foreach ($coolUsers as $coolUser)
		{
			//compute similarity indexes between "me" and selected user
			$sum1 = $sum2a = $sum2b = 0;
			foreach ($lists[$mainUser->id] as $e1)
			{
				if (isset($lists[$coolUser->id][$e1->id]))
				{
					$e2 = $lists[$coolUser->id][$e1->id];
					$tmp1 = ($e1->score - $meanScores[$mainUser->id]);
					$tmp2 = ($e2->score - $meanScores[$coolUser->id]);
					$sum1 += $tmp1 * $tmp2;
					$sum2a += $tmp1 * $tmp1;
					$sum2b += $tmp2 * $tmp2;
				}
			}
			$similarity[$coolUser->id] = $sum1 / max(1, sqrt($sum2a * $sum2b));

			//check what titles are on their list
			foreach ($lists[$coolUser->id] as $e2)
			{
				$add = false;
				if (!isset($lists[$mainUser->id][$e2->id]))
				{
					$add = true;
				}
				else
				{
					$e1 = $lists[$mainUser->id][$e2->id];
					if ($e1->status == UserListEntry::Planned)
					{
						$add = true;
					}
				}
				if ($add)
				{
					$selectedEntries[$e2->id] = $e2;
				}
			}
			#$simNormalize += abs($selUser->sim);
		}

		/*
		$finalAM = [];

		$list = $u->getList(ChibiRegistry::getView()->am);

		//get title ratings
		$finalAM = [];
		foreach ($selAM as $id) {
			$score = 0;
			foreach ($selUsers as $selUser) {
				$e2 = $selUser->list->getEntryByID($id);
				//filter sources
				if ($e2) {
					$score2 = $e2->getScore();
					if ($score2) {
						$score += $selUser->sim * ($score2 - $selUser->meanScore);
					}
				}
			}
			#$score *= $simNormalize;
			$score += $meanScore;
			$finalAM[$id] = $score;
		}
		arsort($finalAM, SORT_NUMERIC);
		$finalAM = array_keys($finalAM);

		//always append at the end shuffled static recommendations
		$staticRecs = ChibiRegistry::getHelper('mg')->loadJSON(ChibiConfig::getInstance()->chibi->runtime->rootFolder . DIRECTORY_SEPARATOR . ChibiConfig::getInstance()->misc->staticRecsDefFile);
		shuffle($staticRecs[ChibiRegistry::getView()->am]);
		$finalAM = array_merge($finalAM, $staticRecs[ChibiRegistry::getView()->am]);

		//now, compute final recommendations
		$limit = 15;
		$recs = [];
		$nonPlannedRecs = 0;
		while (count($finalAM) > 0 and $nonPlannedRecs < $limit) {
			//make sure only first unwatched thing in franchise is going to be recommended
			$franchise = $modelAM->get(array_shift($finalAM))->getFranchise();
			uasort($franchise->entries, function($a, $b) { return $a->getID() > $b->getID() ? 1 : -1; });
			$amEntry = null;
			foreach ($franchise->entries as $franchiseEntry) {
				if ($franchiseEntry->getStatus() == AMEntry::STATUS_NOT_YET_PUBLISHED) {
					continue;
				}
				$userEntry = $u->getList(ChibiRegistry::getView()->am)->getEntryByID($franchiseEntry->getID());
				if (!$userEntry) {
					$amEntry = $franchiseEntry;
					$nonPlannedRecs ++;
					break;
				} elseif ($userEntry->getStatus() == UserListEntry::STATUS_PLANNED) {
					$amEntry = $franchiseEntry;
					break;
				}
			}
			if (empty($amEntry)) {
				continue;
			}
			//don't recommend more than one thing in given franchise
			foreach ($franchise->entries as $franchiseEntry) {
				$id2 = $franchiseEntry->getID();
				$finalAM = array_filter($finalAM, function($id) use ($id2) { return $id != $id2; });
			}
			$rec = new StdClass;
			$rec->userEntry = $userEntry;
			$rec->amEntry = $amEntry;
			$recs []= $rec;
		}

		ChibiRegistry::getHelper('session')->restore();
		ChibiRegistry::getView()->recs[$u->getID()] = $recs;
		*/



		$list = $viewContext->user->getMixedUserMedia($viewContext->media);
		$dontRecommend = [];
		foreach ($list as $entry)
		{
			$dontRecommend[$entry->media . $entry->mal_id] = true;
		}

		$allFranchises = $viewContext->user->getFranchisesFromUserMedia($list, true);
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
			$dist = RatingDistribution::fromEntries($franchise->ownEntries);
			$franchise->meanScore = $dist->getMeanScore();
			$franchises []= $franchise;
		}
		DataSorter::sort($franchises, DataSorter::MeanScore);

		$viewContext->franchises = $franchises;
		$viewContext->private = $viewContext->user->isUserMediaPrivate($viewContext->media);
	}
}
