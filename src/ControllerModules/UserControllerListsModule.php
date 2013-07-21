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

	public static function work(&$viewContext)
	{
		$viewContext->viewName = 'user-list';
		$viewContext->meta->styles []= '/media/css/user/list.css';
		$viewContext->meta->scripts []= 'http://cdn.ucb.org.br/Scripts/tablesorter/jquery.tablesorter.min.js';
		$viewContext->meta->scripts []= '/media/js/user/list.js';
		$viewContext->list = $viewContext->user->getMixedUserMedia($viewContext->media);
		$viewContext->private = $viewContext->user->isUserMediaPrivate($viewContext->media);
	}
}
