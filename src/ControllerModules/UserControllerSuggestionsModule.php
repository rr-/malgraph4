<?php
class UserControllerSuggestionsModule extends AbstractUserControllerModule
{
	public static function getText($media)
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
		$viewContext->meta->styles []= '/media/css/user/suggestions.css';
		$viewContext->meta->scripts []= '/media/js/user/suggestions.js';

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

			usort($franchise->allEntries, function($a, $b) {
				return strcmp($a->media . $a->mal_id, $b->media . $b->mal_id);
			});
			$dist = RatingDistribution::fromEntries($franchise->ownEntries);
			$franchise->meanScore = $dist->getMeanScore();
			$franchises []= $franchise;
		}
		usort($franchises, function($f1, $f2) { return $f2->meanScore > $f1->meanScore ? 1 : -1; });

		$viewContext->franchises = $franchises;
		$viewContext->private = $viewContext->user->isUserMediaPrivate($viewContext->media);
	}
}
