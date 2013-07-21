<?php
class UserControllerProfileModule extends AbstractUserControllerModule
{
	public static function getText(ViewContext $viewContext)
	{
		return 'Profile';
	}

	public static function getUrlParts()
	{
		return ['', 'profile'];
	}

	public static function getMediaAvailability()
	{
		return [];
	}

	public static function work(&$viewContext)
	{
		$viewContext->viewName = 'user-profile';
		$viewContext->meta->styles []= '/media/css/user/profile.css';
		$viewContext->meta->scripts []= '/media/js/user/profile.js';
		$viewContext->meta->scripts []= '/media/js/user/entries.js';

		$viewContext->yearsOnMal = null;
		if (intval($viewContext->user->join_date))
		{
			list ($year, $month, $day) = explode('-', $viewContext->user->join_date);
			$time = mktime(0, 0, 0, $month, $day, $year);
			$diff = time() - $time;
			$diff /= 3600 * 24;
			$viewContext->yearsOnMal = $diff / 361.25;
		}

		$viewContext->friends = $viewContext->user->ownUserfriend;
		usort($viewContext->friends, function($a, $b) {
			return strcasecmp($a->name, $b->name);
		});

		$viewContext->clubs = $viewContext->user->ownUserclub;
		usort($viewContext->clubs, function($a, $b) {
			return strcasecmp($a->name, $b->name);
		});

		$viewContext->completed = [];
		$viewContext->meanUserScore = [];
		$viewContext->meanGlobalScore = [];
		$viewContext->franchiseCount = [];
		$viewContext->mismatchedCount = [];
		foreach (Media::getConstList() as $media)
		{
			$list = $viewContext->user->getMixedUserMedia($media);
			$listFinished = array_filter($list, function($mixedMediaEntry) { return $mixedMediaEntry->status == UserListStatus::Finished; });
			$listNonPlanned = array_filter($list, function($mixedMediaEntry) { return $mixedMediaEntry->status != UserListStatus::Planned; });

			$viewContext->completed[$media] = count($listFinished);
			$viewContext->meanUserScore[$media] = RatingDistribution::fromEntries($listNonPlanned)->getMeanScore();
			$viewContext->meanGlobalScore[$media] = Model_MixedUserMedia::getRatingDistribution($media)->getMeanScore();
			if ($media == Media::Anime)
			{
				$viewContext->episodes = array_sum(array_map(function($mixedMediaEntry) { return $mixedMediaEntry->finished_episodes; }, $listFinished));
			}
			else
			{
				$viewContext->chapters = array_sum(array_map(function($mixedMediaEntry) { return $mixedMediaEntry->finished_chapters; }, $listFinished));
			}
			$franchises = $viewContext->user->getFranchisesFromUserMedia($listNonPlanned);
			$viewContext->franchiseCount[$media] = count(array_filter($franchises, function($franchise) { return count($franchise->ownEntries) > 1; }));

			$mismatched = $viewContext->user->getMismatchedUserMedia($list);
			$viewContext->mismatchedCount[$media] = count($mismatched);
		}
	}
}
