<?php
require_once 'singleton.php';

class ErrorHandler extends Singleton
{
	public static function suppress()
	{
		set_error_handler(function($errno, $errstr, $errfile, $errline) {});
	}

	public static function restore()
	{
	}

	protected static function doInit()
	{
		set_error_handler(function($errorId, $errorString, $errorFile, $errorLine)
		{
			throw new ErrorException($errorString, $errorId, 0, $errorFile, $errorLine);
		});
	}
}

ErrorHandler::init();
