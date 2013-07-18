<?php
class UserControllerProfileModule extends AbstractUserControllerModule
{
	public static function getText()
	{
		return 'Profile';
	}

	public static function getUrlParts()
	{
		return ['', 'profile'];
	}

	public static function getMediaAvailability()
	{
		return [];
	}

	public static function work(&$viewContext)
	{
		$viewContext->viewName = 'user-profile';
		$viewContext->meta->styles []= '/media/css/user/profile.css';
		$viewContext->meta->scripts []= '/media/js/user/profile.js';

		$result = Retriever::getUser($viewContext->userId);

		$viewContext->processed = strtotime($result->processed);
		$viewContext->userGender = strtotime($result->gender);
		$viewContext->animeViewCount = $result->anime_views;
		$viewContext->mangaViewCount = $result->manga_views;
		$viewContext->joinDate = $result->join_date;

		$viewContext->friends = Retriever::getUserFriends($viewContext->userId);
		usort($viewContext->friends, function($a, $b) {
			return strcasecmp($a->name, $b->name);
		});

		$viewContext->clubs = Retriever::getUserClubs($viewContext->userId);
		usort($viewContext->clubs, function($a, $b) {
			return strcasecmp($a->name, $b->name);
		});
	}
}
