<?php
class UserControllerProfileModule extends AbstractUserControllerModule
{
	public static function getText($media)
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

		$viewContext->yearsOnMal = null;
		if (intval($viewContext->user->join_date))
		{
			list ($year, $month, $day) = explode('-', $viewContext->user->join_date);
			$time = mktime(0, 0, 0, $month, $day, $year);
			$diff = time() - $time;
			$diff /= 3600 * 24;
			$viewContext->yearsOnMal = $diff / 361.25;
		}

		$viewContext->friends = $viewContext->user->ownUserfriend;
		usort($viewContext->friends, function($a, $b) {
			return strcasecmp($a->name, $b->name);
		});

		$viewContext->clubs = $viewContext->user->ownUserclub;
		usort($viewContext->clubs, function($a, $b) {
			return strcasecmp($a->name, $b->name);
		});
	}
}
