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
		$viewContext->ratingDistribution = new RatingDistribution($list);
		$viewContext->private = Retriever::isUserMediaListPrivate($viewContext->userId, $viewContext->media);
	}
}
