<?php
class MediaSubProcessorGenres extends MediaSubProcessor
{
	public function process(array $documents, &$context)
	{
		$doc = self::getDOM($documents[self::URL_MEDIA]);
		$xpath = new DOMXPath($doc);

		$this->delete('mediagenre', ['media_id' => $context->media->id]);
		$data = [];
		foreach ($xpath->query('//span[starts-with(text(), \'Genres\')]/../a') as $node)
		{
			preg_match('/=([0-9]+)/', $node->getAttribute('href'), $matches);
			$genreMalId = Strings::makeInteger($matches[1]);
			$genreName = Strings::removeSpaces($node->textContent);
			$data []= [
				'media_id' => $context->media->id,
				'mal_id' => $genreMalId,
				'name' => $genreName
			];
		}
		$this->insert('mediagenre', $data);
	}
}
