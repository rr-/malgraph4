<?php
class UserProcessor extends AbstractProcessor
{
	public function beforeProcessing(&$context)
	{
		$context->user = R::findOrDispense('user', 'LOWER(name) = LOWER(?)', [$context->key]);
		if (is_array($context->user))
		{
			$context->user = reset($context->user);
		}
	}

	public function afterProcessing(&$context)
	{
		R::store($context->user);
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
