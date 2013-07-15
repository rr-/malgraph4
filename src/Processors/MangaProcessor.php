<?php
class MangaProcessor extends AbstractProcessor
{
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
