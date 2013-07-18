<?php
class UserControllerListsModule extends AbstractUserControllerModule
{
	public static function getText()
	{
		return 'List';
	}

	public static function getUrlParts()
	{
		return ['list', 'lists'];
	}

	public static function getMediaAvailability()
	{
		return [Media::Anime, Media::Manga];
	}

	public static function work(&$viewContext)
	{
		$viewContext->viewName = 'user-list';
		$viewContext->meta->styles []= '/media/css/user/list.css';
		$viewContext->meta->scripts []= 'http://cdn.ucb.org.br/Scripts/tablesorter/jquery.tablesorter.min.js';
		$viewContext->meta->scripts []= '/media/js/user/list.js';
		$viewContext->list = Retriever::getUserMediaList($viewContext->userId, $viewContext->media);
		$viewContext->private = Retriever::isUserMediaListPrivate($viewContext->userId, $viewContext->media);
	}
}
