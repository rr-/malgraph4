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
	}
}
