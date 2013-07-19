<?php
class UserProcessor extends AbstractProcessor
{
	public function beforeProcessing(&$context)
	{
		$user = R::findOne('user', 'LOWER(name) = LOWER(?)', [$context->key]);
		if (empty($user))
		{
			$user = R::dispense('user');
			$user->name = $context->key;
			R::store($user);
		}
		$context->user = $user;
	}

	public function getSubProcessors()
	{
		$subProcessors = [];
		$subProcessors []= new UserSubProcessorProfile();
		$subProcessors []= new UserSubProcessorFriends();
		$subProcessors []= new UserSubProcessorClubs();
		$subProcessors []= new UserSubProcessorHistory();
		$subProcessors []= new UserSubProcessorUserMedia();
		return $subProcessors;
	}
}
