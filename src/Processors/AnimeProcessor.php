<?php
class AnimeProcessor extends AbstractProcessor
{
	public function getSubProcessors()
	{
		$subProcessors = [];
		$subProcessors []= new MediaSubProcessorBasic(Media::Anime);
		$subProcessors []= new MediaSubProcessorGenres(Media::Anime);
		$subProcessors []= new MediaSubProcessorTags(Media::Anime);
		$subProcessors []= new MediaSubProcessorRelations(Media::Anime);
		$subProcessors []= new AnimeSubProcessorBasic();
		$subProcessors []= new AnimeSubProcessorProducers();
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
