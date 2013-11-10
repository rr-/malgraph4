<?php
class BadProcessorDocumentException extends Exception
{
	public function __construct(Document $document, $message)
	{
		parent::__construct('Bad document (' . $message . ') in ' . $document->url);
	}
}
