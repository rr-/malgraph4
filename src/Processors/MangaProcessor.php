<?php
class MangaProcessor extends AbstractProcessor
{
	public function beforeProcessing(&$context)
	{
		$media = R::findOne('media', 'mal_id = ? AND media = ?', [$context->key, Media::Manga]);
		if (empty($media))
		{
			$media = R::dispense('media');
			$media->mal_id = $context->key;
			$media->media = Media::Manga;
			R::store($media);
		}
		$context->media = $media;
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
