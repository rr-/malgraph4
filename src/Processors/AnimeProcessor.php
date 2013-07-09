<?php
class AnimeProcessor implements AbstractProcessor
{
	public function process($id)
	{
		if (empty($id))
		{
			return;
		}

		$processors = [];
		$processors []= new MediaSubProcessorBasic(Media::Anime);
		$processors []= new MediaSubProcessorGenres(Media::Anime);
		$processors []= new MediaSubProcessorTags(Media::Anime);
		$processors []= new MediaSubProcessorRelations(Media::Anime);
		$processors []= new AnimeSubProcessorBasic();
		$processors []= new AnimeSubProcessorProducers();

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
