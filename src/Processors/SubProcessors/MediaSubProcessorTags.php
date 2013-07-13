<?php
class MediaSubProcessorTags extends MediaSubProcessor
{
	public function process(array $documents, &$context)
	{
		$doc = self::getDOM($documents[self::URL_MEDIA]);
		$xpath = new DOMXPath($doc);
		$data = [];
		foreach ($xpath->query('//h2[starts-with(text(), \'Popular Tags\')]/following-sibling::*/a') as $node)
		{
			$tagName = Strings::removeSpaces($node->textContent);
			$tagCount = Strings::makeInteger($node->getAttribute('title'));
			$data []= ['media_id' => $context->mediaId, 'tag_name' => $tagName, 'tag_count' => $tagCount];
		}
		$this->insert('media_tags', $data);
	}
}
