<?php
class IndexControllerGlobalsModule extends AbstractControllerModule
{
	public static function getUrlParts()
	{
		return ['s/globals'];
	}

	public static function url()
	{
		return '/s/globals';
	}

	public static function work(&$viewContext)
	{
		$viewContext->viewName = 'globals';
		$viewContext->meta->title = 'MALgraph - global statistics';
		$viewContext->meta->styles []= '/media/css/infobox.css';
		$viewContext->meta->styles []= '/media/css/index/globals.css';
		$viewContext->meta->scripts []= 'http://code.highcharts.com/highcharts.js';
		$viewContext->meta->scripts []= '/media/js/highcharts-mg.js';

		$viewContext->userCount = Model_User::getCount();
		$viewContext->mediaCount = [];
		$viewContext->ratingDistribution = [];
		foreach (Media::getConstList() as $media)
		{
			$viewContext->mediaCount[$media] = Model_Media::getCount($media);
			$viewContext->ratingDistribution[$media] = Model_MixedUserMedia::getRatingDistribution($media);
		}
	}
}
