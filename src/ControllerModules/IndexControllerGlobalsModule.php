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
		$viewContext->viewName = 'index-globals';
		$viewContext->meta->title = 'MALgraph - global statistics';
		$viewContext->meta->description = 'Global community statistics on MALgraph, an online tool that extends your MyAnimeList profile.';
		WebMediaHelper::addHighcharts($viewContext);
		WebMediaHelper::addInfobox($viewContext);
		WebMediaHelper::addCustom($viewContext);

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
