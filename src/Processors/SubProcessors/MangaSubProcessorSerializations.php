<?php
class MangaSubProcessorSerializations extends MediaSubProcessor
{
	public function __construct()
	{
		parent::__construct(Media::Manga);
	}

	public function process(array $documents, &$context)
	{
		$doc = self::getDOM($documents[self::URL_MEDIA]);
		$xpath = new DOMXPath($doc);

		$q = $xpath->query('//span[starts-with(text(), \'Serialization\')]/../a');
		if ($q->length > 0)
		{
			$node = $q->item(0);
			preg_match('/=([0-9]+)/', $node->getAttribute('href'), $matches);
			$serializationId = Strings::makeInteger($matches[1]);
			$serializationName = Strings::removeSpaces($q->item(0)->nodeValue);
		}
	}
}

