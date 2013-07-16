<?php
abstract class AbstractUserControllerModule
{
	/**
	* This method contains all code that is executed whenver user visits
	* specific module.
	**/
	public static function work(&$viewContext)
	{
		throw new UnimplementedException();
	}

	/**
	* This method returns the text that is rendered in the menu in layout.
	*/
	public static function getText()
	{
		throw new UnimplementedException();
	}

	/**
	* This method returns what sections of menu in layout should this module be
	* rendered in.
	*/
	public static function getMediaAvailability()
	{
		throw new UnimplementedException();
	}

	/**
	* This method returns what view is responsible for rendering the module.
	*/
	public static function getViewName()
	{
		throw new UnimplementedException();
	}

	/**
	* This method returns what kind of text is allowed within URL to get
	* specific module to run. For example, returning ['list', 'lists'] will
	* trigger this module for example.com/nick/list as well as
	* example.com/nick/lists.
	*/
	public static function getUrlParts()
	{
		throw new UnimplementedException();
	}

	/**
	* This method constructs the URL that is going to be used in layouts,
	* views, etc.
	*/
	public static function url($userName, $media)
	{
		$urlParts = static::getUrlParts();
		$bestPart = array_shift($urlParts);
		while (empty($bestPart) and !empty($urlParts))
		{
			$bestPart = array_shift($urlParts);
		}
		$url = '/' . $userName;
		$url .= '/' . $bestPart;
		if (!empty(static::getMediaAvailability()))
		{
			$url .= ',' . Media::toString($media);
		}
		return UrlHelper::absoluteUrl($url);
	}
}
