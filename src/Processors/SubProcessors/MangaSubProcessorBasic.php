<?php
class MangaSubProcessorBasic extends MediaSubProcessor
{
	public function __construct()
	{
		parent::__construct(Media::Manga);
	}

	public function process(array $documents)
	{
		$doc = self::getDOM($documents[self::URL_MEDIA]);
		$xpath = new DOMXPath($doc);

		//chapter count
		preg_match_all('/([0-9]+|Unknown)/', self::getNodeValue($xpath, '//span[starts-with(text(), \'Chapter\')]/following-sibling::node()[self::text()]'), $matches);
		$chapterCount = Strings::makeInteger($matches[0][0]);

		//volume count
		preg_match_all('/([0-9]+|Unknown)/', self::getNodeValue($xpath, '//span[starts-with(text(), \'Volume\')]/following-sibling::node()[self::text()]'), $matches);
		$volumeCount = Strings::makeInteger($matches[0][0]);
	}
}
