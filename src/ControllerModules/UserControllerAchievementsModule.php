<?php
class UserControllerAchievementsModule extends AbstractUserControllerModule
{
	public static function getText(ViewContext $viewContext, $media)
	{
		return 'Achievements';
	}

	public static function getUrlParts()
	{
		return ['ach', 'achi', 'achievement', 'achievements'];
	}

	public static function getMediaAvailability()
	{
		return [Media::Anime, Media::Manga];
	}

	public static function getOrder()
	{
		return 6;
	}

	private static function getThreshold($achievement)
	{
		$threshold = $achievement->threshold;
		if (preg_match('/^([0-9.]+)\+$/', $threshold, $matches))
		{
			return [floatval($matches[1]), null];
		}
		elseif (preg_match('/^([0-9.]+)(\.\.|-)([0-9.]+)$/', $threshold, $matches))
		{
			return [floatval($matches[1]), floatval($matches[3])];
		}
		throw new Exception('Invalid threshold: ' . $threshold);
	}

	public static function work(&$viewContext)
	{
		$viewContext->viewName = 'user-achievements';
		$viewContext->meta->title = 'MALgraph - ' . $viewContext->user->name . ' - achievements (' . Media::toString($viewContext->media) . ')';
		$viewContext->meta->description = $viewContext->user->name . '&rsquo;s ' . Media::toString($viewContext->media) . ' achievements on MALgraph, an online tool that extends your MyAnimeList profile.';
		$viewContext->meta->keywords = array_merge($viewContext->meta->keywords, ['profile', 'list', 'achievements', 'ratings', 'activity', 'favorites', 'suggestions', 'recommendations']);
		$viewContext->meta->styles []= '/media/css/user/achievements.css';
		$viewContext->meta->styles []= '/media/css/user/general.css';
		$viewContext->meta->scripts []= '/media/js/user/entries.js';
		$viewContext->meta->scripts []= '/media/js/user/achievements.js';

		$achList = TextHelper::loadJson(Config::$achievementsDefinitionPath);
		$imgFiles = scandir(Config::$achievementsImageDir);

		$list = $viewContext->user->getMixedUserMedia($viewContext->media);
		$listFinished = UserMediaFilter::doFilter($list, UserMediaFilter::finished());
		$listNonPlanned = UserMediaFilter::doFilter($list, UserMediaFilter::nonPlanned());

		$evaluators =
		[
			'given-titles' => function($groupData) use ($listFinished)
			{
				$entriesOwned = UserMediaFilter::doFilter($listFinished, UserMediaFilter::givenMedia($groupData->requirement->titles));
				return [count($entriesOwned), $entriesOwned];
			},

			'genre-titles' => function($groupData) use ($viewContext, $listFinished)
			{
				$entriesOwned1 = UserMediaFilter::doFilter($listFinished, UserMediaFilter::genre($groupData->requirement->genre, $listFinished));
				$entriesOwned2 = UserMediaFilter::doFilter($listFinished, UserMediaFilter::givenMedia($groupData->requirement->titles));
				$entriesOwned = array_merge($entriesOwned1, $entriesOwned2);
				#array unique w/ callback
				$entriesOwned = array_intersect_key($entriesOwned, array_unique(array_map(function($e) { return $e->media . $e->mal_id; }, $entriesOwned)));
				return [count($entriesOwned), $entriesOwned];
			},

			'finished-titles' => function($groupData) use ($listFinished)
			{
				return [count($listFinished), null];
			},

			'mean-score' => function($groupData) use ($listNonPlanned)
			{
				$distribution = RatingDistribution::fromEntries($listNonPlanned);
				if ($distribution->getRatedCount() > 0)
				{
					return [$distribution->getMeanScore(), null];
				}
				return [null, null];
			},
		];

		$achievements = [];
		$anyHidden = 0;
		foreach ($achList->{Media::toString($viewContext->media)} as $group => $groupData)
		{
			//get subject and entries basing on requirement type
			$evaluator = $evaluators[$groupData->requirement->type];
			list ($subject, $entriesOwned) = $evaluator($groupData);

			if ($subject === null)
			{
				continue;
			}

			$prevAch = null;
			foreach ($groupData->achievements as &$ach)
			{
				$ach->next = null;
			}
			foreach ($groupData->achievements as &$ach)
			{
				if ($prevAch !== null)
				{
					$prevAch->next = $ach;
				}
				$ach->prev = $prevAch;
				$prevAch = &$ach;
			}
			unset($ach);
			unset($prevAch);
			$groupData->achievements = array_reverse($groupData->achievements);

			//give first achievement for which the subject fits into its threshold
			$localAchievements = [];
			foreach ($groupData->achievements as &$ach)
			{
				list($a, $b) = self::getThreshold($ach);
				$ach->thresholdLeft = $a;
				$ach->thresholdRight = $b;
				$ach->earned = ((($subject >= $a) or ($a === null)) and (($subject <= $b) or ($b === null)));
				if ($ach->next and $ach->next->earned)
				{
					$ach->earned = true;
					$ach->hidden = true;
					$anyHidden = true;
				}
				else
				{
					$ach->hidden = false;
				}

				if ($ach->earned)
				{
					//put additional info
					if (!empty($entriesOwned))
					{
						DataSorter::sort($entriesOwned, DataSorter::Title);
						$ach->entries = $entriesOwned;
					}
					foreach ($imgFiles as $f)
					{
						if (preg_match('/' . $ach->id . '[^0-9a-zA-Z_-]/', $f))
						{
							$ach->path = $f;
						}
					}
					$ach->progress = 100;
					$ach->subject = round($subject, 2);
					if ($ach->next)
					{
						$ach->progress = ($subject - $a) * 100.0 / ($ach->next->thresholdLeft - $a);
					}
					$localAchievements []= $ach;
				}
			}

			$achievements = array_merge($achievements, array_reverse($localAchievements));
		}
		$viewContext->achievements = $achievements;
		$viewContext->private = $viewContext->user->isUserMediaPrivate($viewContext->media);
		$viewContext->anyHidden = $anyHidden;
	}
}
