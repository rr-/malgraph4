<?php
class UserControllerRatingsModule extends AbstractUserControllerModule
{
	public static function getText()
	{
		return 'Ratings';
	}

	public static function getUrlParts()
	{
		return ['rati', 'rating', 'ratings'];
	}

	public static function getMediaAvailability()
	{
		return [Media::Anime, Media::Manga];
	}

	public static function work(&$viewContext)
	{
		$viewContext->viewName = 'user-ratings';
		$viewContext->meta->styles []= '/media/css/infobox.css';
		$viewContext->meta->styles []= '/media/css/user/ratings.css';
		$viewContext->meta->scripts []= 'http://code.highcharts.com/highcharts.js';
		$viewContext->meta->scripts []= '/media/js/highcharts-mg.js';
		$viewContext->meta->scripts []= '/media/js/user/ratings.js';
		$list = Retriever::getUserMediaList($viewContext->userId, $viewContext->media);
		$list = array_filter($list, function($entry) {
			return $entry->status != UserListStatus::Planned;
		});
		$viewContext->ratingDistribution = new RatingDistribution($list);
		$viewContext->ratingTimeDistribution = new RatingTimeDistribution($list);
		$viewContext->lengthDistribution = new MediaLengthDistribution($list);

		$result = Retriever::getUser($viewContext->userId);

		list($year, $month, $day) = explode('-', $result->join_date);
		$earliest = mktime(0, 0, 0, $month, $day, $year);
		$totalTime = 0;
		foreach ($list as $e)
		{
			$totalTime += Retriever::getCompletedDuration($e);
			foreach ([$e->start_date, $e->end_date] as $k)
			{
				$f = explode('-', $k);
				if (count($f) != 3) {
					continue;
				}
				$year = intval($f[0]);
				$month = intval($f[1]);
				$day = intval($f[2]);
				if (!$year or !$month or !$day)
				{
					continue;
				}
				$time = mktime(0, 0, 0, $month, $day, $year);
				if ($time < $earliest) {
					$earliest = $time;
				}
			}
		}

		$viewContext->earliestTimeKnown = $earliest;
		$viewContext->meanTime = $totalTime / max(1, (time() - $earliest) / (24. * 3600.0));
	}
}
