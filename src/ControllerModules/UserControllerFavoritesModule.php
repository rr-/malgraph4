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

	private static function evaluateDistribution(AbstractDistribution $dist)
	{
		$values = [];
		$allEntries = $dist->getAllEntries();
		$meanScore = self::getMeanScore($allEntries);
		foreach ($dist->getGroupsKeys() as $key) {
			$entry = [];
			$ratingDist = RatingDistribution::fromEntries($dist->getGroupEntries($key));
			$localMeanScore = $ratingDist->getRatedCount() * $ratingDist->getMeanScore() + $ratingDist->getUnratedCount() * $meanScore;
			$localMeanScore /= (float)max(1, $dist->getGroupSize($key));
			$weight = $dist->getGroupSize($key) / max(1, $dist->getLargestGroupSize());
			$weight = 1 - pow(1 - pow($weight, 8. / 9.), 2);
			$value = $meanScore + ($localMeanScore - $meanScore) * $weight;
			$values[$key->id] = $value;
		}
		return $values;
	}

	public static function work(&$viewContext)
	{
		$viewContext->viewName = 'user-favorites';
		$viewContext->meta->title = 'MALgraph - ' . $viewContext->user->name . ' - favorites (' . Media::toString($viewContext->media) . ')';
		$viewContext->meta->description = $viewContext->user->name . '&rsquo;s ' . Media::toString($viewContext->media) . ' favorites on MALgraph, an online tool that extends your MyAnimeList profile.';
		$viewContext->meta->keywords = array_merge($viewContext->meta->keywords, ['profile', 'list', 'achievements', 'ratings', 'activity', 'favorites', 'suggestions', 'recommendations']);
		$viewContext->meta->styles []= '/media/css/infobox.css';
		$viewContext->meta->styles []= '/media/css/user/favorites.css';
		$viewContext->meta->scripts []= 'http://cdn.ucb.org.br/Scripts/tablesorter/jquery.tablesorter.min.js';
		$viewContext->meta->scripts []= 'http://code.highcharts.com/highcharts.js';
		$viewContext->meta->scripts []= '/media/js/highcharts-mg.js';
		$viewContext->meta->scripts []= '/media/js/user/entries.js';
		$viewContext->meta->scripts []= '/media/js/user/favorites.js';

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
		foreach ($favYears->getGroupsKeys(AbstractDistribution::IGNORE_NULL_KEY) as $key)
		{
			$subEntries = $favYears->getGroupEntries($key);
			$viewContext->yearScores[$key] = self::getMeanScore($subEntries);
		}

		$viewContext->decadeScores = [];
		foreach ($favDecades->getGroupsKeys(AbstractDistribution::IGNORE_NULL_KEY) as $key)
		{
			$subEntries = $favDecades->getGroupEntries($key);
			$viewContext->decadeScores[$key] = self::getMeanScore($subEntries);
		}

		$viewContext->creatorScores = [];
		$viewContext->creatorValues = [];
		$viewContext->creatorTimeSpent = [];
		foreach ($favCreators->getGroupsKeys(AbstractDistribution::IGNORE_NULL_KEY) as $key)
		{
			$subEntries = $favCreators->getGroupEntries($key);
			$viewContext->creatorScores[$key->id] = self::getMeanScore($subEntries);
			$viewContext->creatorTimeSpent[$key->id] = self::getTimeSpent($subEntries);
		}
		$viewContext->creatorValues = self::evaluateDistribution($favCreators);

		$viewContext->genreScores = [];
		$viewContext->genreValues = [];
		$viewContext->genreTimeSpent = [];
		foreach ($favGenres->getGroupsKeys(AbstractDistribution::IGNORE_NULL_KEY) as $key)
		{
			$subEntries = $favGenres->getGroupEntries($key);
			$viewContext->genreScores[$key->id] = self::getMeanScore($subEntries);
			$viewContext->genreTimeSpent[$key->id] = self::getTimeSpent($subEntries);
		}
		$viewContext->genreValues = self::evaluateDistribution($favGenres);
	}
}
