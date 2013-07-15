<?php
function __autoload($className)
{
	$paths =
	[
		__DIR__ . '/' . $className . '.php',
		__DIR__ . '/Processors/SubProcessors/' . $className . '.php',
		__DIR__ . '/Processors/' . $className . '.php',
		__DIR__ . '/Enums/' . $className . '.php',
		__DIR__ . '/Exceptions/' . $className . '.php',
		__DIR__ . '/Controllers/' . $className . '.php',
		__DIR__ . '/Views/' . $className . '.php',
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

$localCore = __DIR__ . '/local.php';
if (file_exists($localCore))
{
	include $localCore;
}
