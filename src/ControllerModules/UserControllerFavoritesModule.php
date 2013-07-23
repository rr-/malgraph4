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

		$list = $viewContext->user->getMixedUserMedia($viewContext->media);
		$listNonPlanned = array_filter($list, function($a) { return $a->status != UserListStatus::Planned; });

		$favYears = MediaYearDistribution::fromEntries($listNonPlanned);
		$favDecades = MediaDecadeDistribution::fromEntries($listNonPlanned);
		$favDecades->addEmptyDecades();
		$favYears->finalize();
		$favDecades->finalize();
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
	}
}
