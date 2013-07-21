<?php
class UserControllerFavoritesModule extends AbstractUserControllerModule
{
	public static function getText(ViewContext $viewContext, $media)
	{
		return 'Favorites';
	}

	public static function getUrlParts()
	{
		return ['fav', 'favs', 'favorites', 'favourites'];
	}

	public static function getMediaAvailability()
	{
		return [Media::Anime, Media::Manga];
	}

	public static function getOrder()
	{
		return 4;
	}

	public static function work(&$viewContext)
	{
	}
}
