<?php
class AnimeSubProcessorProducers extends MediaSubProcessor
{
	public function __construct()
	{
		parent::__construct(Media::Anime);
	}

	public function process(array $documents)
	{
		$doc = self::getDOM($documents[self::URL_MEDIA]);
		$xpath = new DOMXPath($doc);

		foreach ($xpath->query('//span[starts-with(text(), \'Producers\')]/../a') as $node)
		{
			preg_match('/\?p=([0-9]+)/', $node->getAttribute('href'), $matches);
			$producerId = Strings::makeInteger($matches[1]);
			$producerName = Strings::removeSpaces($node->textContent);
		}
	}
}
