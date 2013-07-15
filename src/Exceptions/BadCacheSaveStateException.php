<?php
class BadCacheSaveStateException extends Exception
{
	public function __construct()
	{
		parent::__construct('Bad cache state save!');
	}
}
