<?php
class MediaSubProcessorGenres extends MediaSubProcessor
{
	public function process(array $documents)
	{
		$doc = self::getDOM($documents[self::URL_MEDIA]);
		$xpath = new DOMXPath($doc);

		foreach ($xpath->query('//span[starts-with(text(), \'Genres\')]/../a') as $node)
		{
			preg_match('/=([0-9]+)/', $node->getAttribute('href'), $matches);
			$genreId = Strings::makeInteger($matches[1]);
			$genreName = Strings::removeSpaces($node->textContent);
		}
	}
}
