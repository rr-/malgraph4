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

foreach (Media::getConstList() as $media)
{
	Model_MixedUserMedia::getRatingDistribution($media, true);
}
