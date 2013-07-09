<?php
class MediaSubProcessorTags extends MediaSubProcessor
{
	public function process(array $documents, &$context)
	{
		$doc = self::getDOM($documents[self::URL_MEDIA]);
		$xpath = new DOMXPath($doc);

		foreach ($xpath->query('//h2[starts-with(text(), \'Popular Tags\')]/following-sibling::*/a') as $node)
		{
			$tagCount = Strings::makeInteger($node->getAttribute('title'));
			$tagName = Strings::removeSpaces($node->textContent);
		}
	}
}
