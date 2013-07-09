<?php
abstract class AbstractSubProcessor
{
	static $domCache = [];

	protected static function getDOM(Document $document)
	{
		if (isset(self::$domCache[$document->url]))
		{
			return self::$domCache[$document->url];
		}

		$doc = new DOMDocument;
		$doc->preserveWhiteSpace = false;
		ErrorHandler::suppress();
		$doc->loadHTML($document->content);
		ErrorHandler::restore();

		if (count(self::$domCache) > 20)
		{
			self::$domCache = [];
		}
		self::$domCache[$document->url] = $doc;
		return $doc;
	}

	protected static function getNodeValue(DOMXPath $xpath, $query, DOMNode $parentNode = null, $attrib = null)
	{
		$node = $xpath->query($query, $parentNode)->item(0);
		if (!empty($node))
		{
			return $attrib
				? $node->getAttribute($attrib)
				: $node->nodeValue;
		}
		return null;
	}

	public abstract function process(array $documents, &$context);
}
