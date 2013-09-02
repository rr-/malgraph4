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

$cache = new Cache();
$allFiles = $cache->getAllFiles();
$usedFiles = $cache->getUsedFiles();
$unusedFiles = array_diff($allFiles, $usedFiles);
foreach ($unusedFiles as $path)
{
	unlink($path);
}
printf('Deleted: %d, left: %d' . PHP_EOL, count($unusedFiles), count($usedFiles));
