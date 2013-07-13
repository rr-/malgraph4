<?php
class MangaSubProcessorAuthors extends MediaSubProcessor
{
	public function __construct()
	{
		parent::__construct(Media::Manga);
	}

	public function process(array $documents, &$context)
	{
		$doc = self::getDOM($documents[self::URL_MEDIA]);
		$xpath = new DOMXPath($doc);

		$data = [];
		foreach ($xpath->query('//span[starts-with(text(), \'Authors\')]/../a') as $node)
		{
			preg_match('/people\/([0-9]+)\//', $node->getAttribute('href'), $matches);
			$authorMalId = Strings::makeInteger($matches[1]);
			$authorName = Strings::removeSpaces($node->nodeValue);
			$data []= [
				'mal_id' => $authorMalId,
				'name' => $authorName
			];
		}
		$this->insert('manga_authors', $data);
	}
}
