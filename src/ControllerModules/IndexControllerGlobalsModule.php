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

	public static function work(&$controllerContext, &$viewContext)
	{
		$viewContext->viewName = 'index-globals';
		$viewContext->meta->title = 'MALgraph - global statistics';
		$viewContext->meta->description = 'Global community statistics on MALgraph, an online tool that extends your MyAnimeList profile.';
		WebMediaHelper::addHighcharts($viewContext);
		WebMediaHelper::addInfobox($viewContext);
		WebMediaHelper::addMiniSections($viewContext);
		WebMediaHelper::addCustom($viewContext);

		$globalsCache = file_exists(Config::$globalsCachePath)
			? TextHelper::loadJson(Config::$globalsCachePath, true)
			: [];

		$viewContext->userCount = $globalsCache['user-count'];
		$viewContext->mediaCount = $globalsCache['media-count'];
		$viewContext->ratingDistribution = array_map(function($v) { return RatingDistribution::fromArray($v); }, $globalsCache['rating-dist']);
		$viewContext->queuedUserCount = (new Queue(Config::$userQueuePath))->size();
		$viewContext->queueSizes = TextHelper::loadJson(Config::$userQueueSizesPath, true);
	}
}
