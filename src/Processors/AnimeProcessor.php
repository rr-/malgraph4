<?php
class AnimeProcessor extends AbstractProcessor
{
	public function beforeProcessing(&$context)
	{
		$context->media = R::findOrDispense('media', 'mal_id = ? AND media = ?', [$context->key, Media::Anime]);
		if (is_array($context->media))
		{
			$context->media = reset($context->media);
		}
	}

	public function afterProcessing(&$context)
	{
		R::store($context->media);
	}

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
}
