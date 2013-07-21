<?php
class UserControllerHistoryModule extends AbstractUserControllerModule
{
	public static function getText(ViewContext $viewContext)
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
	}
}
