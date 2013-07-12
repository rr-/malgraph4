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

	public function beforeProcessing($context)
	{
		$pdo = Database::getPDO();
		$pdo->exec('BEGIN TRANSACTION');
	}

	public function afterProcessing($context)
	{
		$pdo = Database::getPDO();
		if (!empty($context->exception))
		{
			$pdo->exec('ROLLBACK TRANSACTION');
			return;
		}
		$pdo->exec('COMMIT TRANSACTION');
	}
}
