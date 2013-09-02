<?php
class UserControllerListsModule extends AbstractUserControllerModule
{
	public static function getText(ViewContext $viewContext, $media)
	{
		return ucfirst(Media::toString($media) . ' list');
	}

	public static function getUrlParts()
	{
		return ['list', 'lists'];
	}

	public static function getMediaAvailability()
	{
		return [Media::Anime, Media::Manga];
	}

	public static function getOrder()
	{
		return 1;
	}

	public static function work(&$controllerContext, &$viewContext)
	{
		$viewContext->viewName = 'user-list';
		$viewContext->meta->title = 'MALgraph - ' . $viewContext->user->name . ' - list (' . Media::toString($viewContext->media) . ')';
		$viewContext->meta->description = $viewContext->user->name . '&rsquo;s ' . Media::toString($viewContext->media) . ' list on MALgraph, an online tool that extends your MyAnimeList profile.';
		$viewContext->meta->keywords = array_merge($viewContext->meta->keywords, ['profile', 'list', 'achievements', 'ratings', 'history', 'favorites', 'suggestions', 'recommendations']);
		WebMediaHelper::addTablesorter($viewContext);
		WebMediaHelper::addCustom($viewContext);

		$viewContext->list = $viewContext->user->getMixedUserMedia($viewContext->media);
		$viewContext->private = $viewContext->user->isUserMediaPrivate($viewContext->media);
	}
}
