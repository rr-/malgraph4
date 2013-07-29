<?php
class ControllerContext
{
	public $bypassCache;

	public function __construct()
	{
		$this->bypassCache = false;
	}
}
