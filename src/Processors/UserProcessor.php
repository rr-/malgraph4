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

	public function beforeProcessing()
	{
		$pdo = Database::getPDO();
		$pdo->exec('BEGIN');
	}

	public function afterProcessing()
	{
		$pdo = Database::getPDO();
		$pdo->exec('END');
	}

}
