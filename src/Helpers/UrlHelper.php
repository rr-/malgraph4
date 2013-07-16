<?php
class UrlHelper
{
	public static function absoluteUrl($relativeUrl = null)
	{
		if ($relativeUrl === null)
		{
			$relativeUrl = '/' . ltrim($_SERVER['REQUEST_URI'], '/');
		}
		if (strpos($relativeUrl, '://') !== false)
		{
			$absoluteUrl = $relativeUrl;
		}
		else
		{
			$absoluteUrl = !empty(Config::$baseUrl)
				? Config::$baseUrl
				: $_SERVER['HTTP_HOST'];
			$absoluteUrl = rtrim($absoluteUrl, '/') . '/';
			$absoluteUrl .= ltrim($relativeUrl, '/');
		}
		if (!empty($p))
		{
			$absoluteUrl .= '?' . http_build_query($p);
		}
		$absoluteUrl = preg_replace('/(?<!:)\/\//', '/', $absoluteUrl);
		return $absoluteUrl;
	}

	public static function userModuleUrl($userName, $media, $module)
	{
		$url = '/';
		$url .= $userName;
		$url .= '/';
		switch ($module)
		{
			case UserModule::Profile: return '/' . $userName;
			case UserModule::Lists: $url .= 'list'; break;
			case UserModule::Ratings: $url .= 'rati'; break;
			case UserModule::Activity: $url .= 'acti'; break;
			case UserModule::Favorites: $url .= 'favs'; break;
			case UserModule::Suggestions: $url .= 'sug'; break;
			case UserModule::Achievements: $url .= 'achi'; break;
		}
		$url .= ',';
		$url .= Media::toString($media);
		return self::absoluteUrl($url);
	}
}
