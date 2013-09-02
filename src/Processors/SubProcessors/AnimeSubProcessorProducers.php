<?php
class AnimeSubProcessorProducers extends MediaSubProcessor
{
	public function __construct()
	{
		parent::__construct(Media::Anime);
	}

	public function process(array $documents, &$context)
	{
		$doc = self::getDOM($documents[self::URL_MEDIA]);
		$xpath = new DOMXPath($doc);

		Database::delete('animeproducer', ['media_id' => $context->media->id]);
		$data = [];
		foreach ($xpath->query('//span[starts-with(text(), \'Producers\')]/../a') as $node)
		{
			if (!preg_match('/\?p=([0-9]+)/', $node->getAttribute('href'), $matches))
			{
				continue;
			}
			$producerMalId = Strings::makeInteger($matches[1]);
			$producerName = Strings::removeSpaces($node->textContent);
			$data []= [
				'media_id' => $context->media->id,
				'mal_id' => $producerMalId,
				'name' => $producerName,
			];
		}
		Database::insert('animeproducer', $data);
	}
}
