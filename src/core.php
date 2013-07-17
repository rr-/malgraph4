<?php
function __autoload($className)
{
	$name = $className . '.php';

	$directoryIterator = new RecursiveDirectoryIterator(__DIR__);
	$iterator = new RecursiveIteratorIterator($directoryIterator);
	$dirs = [];
	foreach ($iterator as $file)
	{
		if ($file->isDir())
		{
			$dirs []= $file->getRealPath();
		}
	}
	$dirs = array_unique($dirs);

	foreach ($dirs as $dir)
	{
		$path = $dir . DIRECTORY_SEPARATOR . $name;
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
