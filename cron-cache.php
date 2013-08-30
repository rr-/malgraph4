<?php
require_once 'src/core.php';
try
{
	SingleInstance::run(__FILE__);
}
catch (Exception $e)
{
	echo $e->getMessage() . PHP_EOL;
	exit(1);
}

$deleted = 0;
$left = 0;
foreach (glob(Config::$cachePath . DIRECTORY_SEPARATOR . '*') as $path)
{
	$age = time() - filemtime($path);
	if ($age > Config::$cacheTimeToLive)
	{
		printf('%s - %.02fh' . PHP_EOL, $path, $age / 3600);
		$deleted ++;
		unlink($path);
	}
	else
	{
		$left ++;
	}
}
printf('Deleted: %d, left: %d' . PHP_EOL, $deleted, $left);
