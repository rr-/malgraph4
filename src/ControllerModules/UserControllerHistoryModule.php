<?php
class UserControllerHistoryModule extends AbstractUserControllerModule
{
	public static function getText(ViewContext $viewContext, $media)
	{
		return 'History';
	}

	public static function getUrlParts()
	{
		return ['acti', 'hist', 'activity', 'history'];
	}

	public static function getMediaAvailability()
	{
		return [Media::Anime, Media::Manga];
	}

	public static function getOrder()
	{
		return 3;
	}

	public static function work(&$viewContext)
	{
		$viewContext->viewName = 'user-history';
		$viewContext->meta->title = 'MALgraph - ' . $viewContext->user->name . ' - activity (' . Media::toString($viewContext->media) . ')';
		$viewContext->meta->description = $viewContext->user->name . '&rsquo;s ' . Media::toString($viewContext->media) . ' activity on MALgraph, an online tool that extends your MyAnimeList profile.';
		$viewContext->meta->keywords = array_merge($viewContext->meta->keywords, ['profile', 'list', 'achievements', 'ratings', 'activity', 'favorites', 'suggestions', 'recommendations']);
	}
}
