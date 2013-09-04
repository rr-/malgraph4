<?php
class UserControllerHistoryModule extends AbstractUserControllerModule
{
	public static function getText(ViewContext $viewContext, $media)
	{
		return 'History';
	}

	public static function getUrlParts()
	{
		return ['hist', 'acti', 'history', 'activity'];
	}

	public static function getMediaAvailability()
	{
		return [Media::Anime, Media::Manga];
	}

	public static function getOrder()
	{
		return 3;
	}

	public static function work(&$controllerContext, &$viewContext)
	{
		$viewContext->viewName = 'user-history';
		$viewContext->meta->title = 'MALgraph - ' . $viewContext->user->name . ' - history (' . Media::toString($viewContext->media) . ')';
		$viewContext->meta->description = $viewContext->user->name . '&rsquo;s ' . Media::toString($viewContext->media) . ' activity history on MALgraph, an online tool that extends your MyAnimeList profile.';
		$viewContext->meta->keywords = array_merge($viewContext->meta->keywords, ['profile', 'list', 'achievements', 'ratings', 'history', 'favorites', 'suggestions', 'recommendations']);
		WebMediaHelper::addHighcharts($viewContext);
		WebMediaHelper::addInfobox($viewContext);
		WebMediaHelper::addEntries($viewContext);
		WebMediaHelper::addCustom($viewContext);

		$list = $viewContext->user->getMixedUserMedia($viewContext->media);
		$listFinished = UserMediaFilter::doFilter($list, UserMediaFilter::finished());

		$monthlyHistoryGroups = [];
		$unknownEntries = [];
		$max = 0;
		foreach ($listFinished as $entry)
		{
			$key = $entry->end_date;
			list ($year, $month, $day) = array_map('intval', explode('-', $key));
			if (!$year or !$month)
			{
				$unknownEntries []= $entry;
				continue;
			}

			if (!isset($monthlyHistoryGroups[$year]))
			{
				$monthlyHistoryGroups[$year] = [];
			}
			if (!isset($monthlyHistoryGroups[$year][$month]))
			{
				$monthlyHistoryGroups[$year][$month] = [];
			}
			$monthlyHistoryGroups[$year][$month] []= $entry;
			$max = max($max, count($monthlyHistoryGroups[$year][$month]));
		}

		krsort($monthlyHistoryGroups, SORT_NUMERIC);
		foreach ($monthlyHistoryGroups as &$group)
		{
			ksort($group, SORT_NUMERIC);
		}
		unset($group);
		$viewContext->monthlyHistoryMax = $max;
		$viewContext->monthlyHistoryGroups = $monthlyHistoryGroups;
		$viewContext->monthlyHistoryUnknownEntries = $unknownEntries;

		$dailyHistory = $viewContext->user->getHistory($viewContext->media);
		$dailyHistoryGroups = [];
		foreach ($dailyHistory as $historyEntry)
		{
			$key = date('Y-m-d', strtotime($historyEntry->timestamp));
			if (!isset($dailyHistoryGroups[$key]))
			{
				$dailyHistoryGroups[$key] = [];
			}
			$dailyHistoryGroups[$key] []= $historyEntry;
		}
		krsort($dailyHistoryGroups);
		$viewContext->dailyHistoryGroups = $dailyHistoryGroups;

		$viewContext->isPrivate = $viewContext->user->isUserMediaPrivate($viewContext->media);
	}
}
