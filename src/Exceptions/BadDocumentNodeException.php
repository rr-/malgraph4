<?php
class BadDocumentNodeException extends Exception
{
	public function __construct($document, $node, $value)
	{
		parent::__construct('Bad document node (' . $node . ', value: "' . $value . '") in ' . $document->url);
	}
}
