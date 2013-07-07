<?php
function __autoload($className)
{
	$paths = [
		__DIR__ . '/' . $className . '.php',
		__DIR__ . '/Processors/' . $className . '.php',
		__DIR__ . '/Enums/' . $className . '.php'
	];
	foreach ($paths as $path)
	{
		if (file_exists($path))
		{
			include $path;
		}
	}
}

ErrorHandler::init();
