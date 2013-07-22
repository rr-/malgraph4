<?php
class UserControllerAchievementsModule extends AbstractUserControllerModule
{
	public static function getText(ViewContext $viewContext, $media)
	{
		return 'Achievements';
	}

	public static function getUrlParts()
	{
		return ['ach', 'achi', 'achievement', 'achievements'];
	}

	public static function getMediaAvailability()
	{
		return [Media::Anime, Media::Manga];
	}

	public static function getOrder()
	{
		return 6;
	}

	public static function work(&$viewContext)
	{
		$viewContext->viewName = 'user-achievements';
		$viewContext->meta->title = 'MALgraph - ' . $viewContext->user->name . ' - achievements (' . Media::toString($viewContext->media) . ')';
		$viewContext->meta->description = $viewContext->user->name . '&rsquo;s ' . Media::toString($viewContext->media) . ' achievements on MALgraph, an online tool that extends your MyAnimeList profile.';
		$viewContext->meta->keywords = array_merge($viewContext->meta->keywords, ['profile', 'list', 'achievements', 'ratings', 'activity', 'favorites', 'suggestions', 'recommendations']);
	}
}
