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
}
