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

		$franchises = $viewContext->user->getFranchisesFromUserMedia($list, true);
		$viewContext->franchises = [];
		foreach ($franchises as &$franchise)
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
			if (!empty($franchise->allEntries))
			{
				$viewContext->franchises []= $franchise;
			}
		}
		$viewContext->private = $viewContext->user->isUserMediaPrivate($viewContext->media);
	}
}
