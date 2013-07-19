<?php
class MangaProcessor extends AbstractProcessor
{
	public function beforeProcessing(&$context)
	{
		$context->media = R::findOrDispense('media', 'mal_id = ? AND media = ?', [$context->key, Media::Manga]);
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
		$subProcessors []= new MediaSubProcessorBasic(Media::Manga);
		$subProcessors []= new MediaSubProcessorGenres(Media::Manga);
		$subProcessors []= new MediaSubProcessorTags(Media::Manga);
		$subProcessors []= new MediaSubProcessorRelations(Media::Manga);
		$subProcessors []= new MangaSubProcessorBasic();
		$subProcessors []= new MangaSubProcessorAuthors();
		return $subProcessors;
	}
}
