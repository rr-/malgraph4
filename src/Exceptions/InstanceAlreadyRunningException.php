<?php
class InstanceAlreadyRunningException extends Exception
{
	public function __construct()
	{
		parent::__construct('An instance of this script is already running!');
	}
}
