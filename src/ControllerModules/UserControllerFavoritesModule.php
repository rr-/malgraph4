<?php
class UserControllerFavoritesModule extends AbstractUserControllerModule
{
	public static function getText(ViewContext $viewContext, $media)
	{
		return 'Favorites';
	}

	public static function getUrlParts()
	{
		return ['fav', 'favs', 'favorites', 'favourites'];
	}

	public static function getMediaAvailability()
	{
		return [Media::Anime, Media::Manga];
	}

	public static function getOrder()
	{
		return 4;
	}

	private static function getMeanScore($entries)
	{
		$tmpDist = RatingDistribution::fromEntries($entries);
		return $tmpDist->getMeanScore();
	}

	private static function getTimeSpent($entries)
	{
		$sum = 0;
		foreach ($entries as $entry)
		{
			$sum += $entry->completed_duration;
		}
		return $sum;
	}

	public static function work(&$viewContext)
	{
		$viewContext->viewName = 'user-favorites';
		$viewContext->meta->title = 'MALgraph - ' . $viewContext->user->name . ' - favorites (' . Media::toString($viewContext->media) . ')';
		$viewContext->meta->description = $viewContext->user->name . '&rsquo;s ' . Media::toString($viewContext->media) . ' favorites on MALgraph, an online tool that extends your MyAnimeList profile.';
		$viewContext->meta->keywords = array_merge($viewContext->meta->keywords, ['profile', 'list', 'achievements', 'ratings', 'activity', 'favorites', 'suggestions', 'recommendations']);
		WebMediaHelper::addHighcharts($viewContext);
		WebMediaHelper::addTablesorter($viewContext);
		WebMediaHelper::addInfobox($viewContext);
		WebMediaHelper::addEntries($viewContext);
		WebMediaHelper::addCustom($viewContext);

		$list = $viewContext->user->getMixedUserMedia($viewContext->media);
		$listNonPlanned = UserMediaFilter::doFilter($list, UserMediaFilter::nonPlanned());

		$favCreators = MediaCreatorDistribution::fromEntries($listNonPlanned);
		$favGenres = MediaGenreDistribution::fromEntries($listNonPlanned);
		$favYears = MediaYearDistribution::fromEntries($listNonPlanned);
		$favDecades = MediaDecadeDistribution::fromEntries($listNonPlanned);
		$viewContext->favCreators = $favCreators;
		$viewContext->favGenres = $favGenres;
		$viewContext->favYears = $favYears;
		$viewContext->favDecades = $favDecades;

		$viewContext->yearScores = [];
		foreach ($favYears->getGroupsKeys() as $safeKey => $key)
		{
			$subEntries = $favYears->getGroupEntries($key);
			$viewContext->yearScores[$safeKey] = self::getMeanScore($subEntries);
		}

		$viewContext->decadeScores = [];
		foreach ($favDecades->getGroupsKeys(AbstractDistribution::IGNORE_NULL_KEY) as $safeKey => $key)
		{
			$subEntries = $favDecades->getGroupEntries($key);
			$viewContext->decadeScores[$safeKey] = self::getMeanScore($subEntries);
		}

		$viewContext->creatorScores = [];
		$viewContext->creatorValues = [];
		$viewContext->creatorTimeSpent = [];
		foreach ($favCreators->getGroupsKeys(AbstractDistribution::IGNORE_NULL_KEY) as $safeKey => $key)
		{
			$subEntries = $favCreators->getGroupEntries($key);
			$viewContext->creatorScores[$safeKey] = self::getMeanScore($subEntries);
			$viewContext->creatorTimeSpent[$safeKey] = self::getTimeSpent($subEntries);
		}
		$viewContext->creatorValues = DistributionEvaluator::evaluate($favCreators);

		$viewContext->genreScores = [];
		$viewContext->genreValues = [];
		$viewContext->genreTimeSpent = [];
		foreach ($favGenres->getGroupsKeys(AbstractDistribution::IGNORE_NULL_KEY) as $safeKey => $key)
		{
			$subEntries = $favGenres->getGroupEntries($key);
			$viewContext->genreScores[$safeKey] = self::getMeanScore($subEntries);
			$viewContext->genreTimeSpent[$safeKey] = self::getTimeSpent($subEntries);
		}
		$viewContext->genreValues = DistributionEvaluator::evaluate($favGenres);
	}
}
