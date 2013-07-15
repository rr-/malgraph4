<?php
function __autoload($className)
{
	$name = $className . '.php';
	$dirs =
	[
		'',
		'Processors' . DIRECTORY_SEPARATOR . 'SubProcessors',
		'Processors',
		'Enums',
		'Exceptions',
		'Controllers',
		'Views',
	];
	foreach ($dirs as $dir)
	{
		$path = implode(DIRECTORY_SEPARATOR, [__DIR__, $dir, $name]);
		if (file_exists($path))
		{
			include $path;
		}
	}
}

ErrorHandler::init();

$localCore = __DIR__ . DIRECTORY_SEPARATOR . 'local.php';
if (file_exists($localCore))
{
	include $localCore;
}
