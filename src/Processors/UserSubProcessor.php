<?php
abstract class UserSubProcessor implements Processor
{
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

	protected static function getDOM($document)
	{
		$doc = new DOMDocument;
		$doc->preserveWhiteSpace = false;
		ErrorHandler::suppress();
		$doc->loadHTML($document->content);
		ErrorHandler::restore();
		return $doc;
	}
}
