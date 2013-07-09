<?php
class MangaProcessor implements AbstractProcessor
{
	public function process($id)
	{
		if (empty($id))
		{
			return;
		}

		$processors = [];
		$processors []= new MediaSubProcessorBasic(Media::Manga);
		$processors []= new MediaSubProcessorGenres(Media::Manga);
		$processors []= new MediaSubProcessorTags(Media::Manga);
		$processors []= new MediaSubProcessorRelations(Media::Manga);
		$processors []= new MangaSubProcessorBasic();
		$processors []= new MangaSubProcessorAuthors();
		$processors []= new MangaSubProcessorSerializations();

		$urls = [];
		foreach ($processors as $processor)
		{
			$urls[get_class($processor)] = $processor->getURLs($id);
		}

		$downloader = new Downloader();
		$documents = $downloader->downloadMulti($urls);

		foreach ($processors as $processor)
		{
			$processor->process($documents[get_class($processor)]);
		}
	}
}

