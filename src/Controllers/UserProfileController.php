<?php
class UserProfileController extends UserController
{
	public static function moduleMatch()
	{
		return ['', 'profile'];
	}

	public function doWork($url)
	{
		parent::doWork($url);

		#$this->user;
		#$this->media;
	}
}
