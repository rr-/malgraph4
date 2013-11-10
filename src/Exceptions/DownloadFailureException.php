<?php
class DownloadFailureException extends Exception
{
	public function __construct(Document $document, $reason = null)
	{
		$msg = $reason
			? sprintf('Download failure: %s (%d; reason=%s)', $document->url, $document->code, $reason)
			: sprintf('Download failure: %s (%d)', $document->url, $document->code);
		parent::__construct($msg);
	}
}
