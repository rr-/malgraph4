<?php
class MangaSubProcessorAuthors extends MediaSubProcessor
{
	public function __construct()
	{
		parent::__construct(Media::Manga);
	}

	public function process($documents)
	{
		$doc = self::getDOM($documents[self::URL_MEDIA]);
		$xpath = new DOMXPath($doc);

		foreach ($xpath->query('//span[starts-with(text(), \'Authors\')]/../a') as $node)
		{
			preg_match('/people\/([0-9]+)\//', $node->getAttribute('href'), $matches);
			$authorId = Strings::makeInteger($matches[1]);
			$authorName = Strings::removeSpaces($node->nodeValue);
		}
	}
}
