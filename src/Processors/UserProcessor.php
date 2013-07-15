<?php
class UserProcessor extends AbstractProcessor
{
	public function getSubProcessors()
	{
		$subProcessors = [];
		$subProcessors []= new UserSubProcessorProfile();
		$subProcessors []= new UserSubProcessorClubs();
		$subProcessors []= new UserSubProcessorFriends();
		$subProcessors []= new UserSubProcessorHistory();
		$subProcessors []= new UserSubProcessorLists();
		return $subProcessors;
	}
}
