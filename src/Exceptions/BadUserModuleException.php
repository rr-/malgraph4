<?php
class BadUserModuleException extends Exception
{
	public function __construct()
	{
		parent::__construct('Unknown module!');
	}
}
