<?php
class DownloadFailureException extends Exception
{
	public function __construct($document)
	{
		parent::__construct(sprintf('Download failure: %s (%d)', $document->url, $document->code));
	}
}
