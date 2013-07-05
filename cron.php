<?php
require_once 'src/processor.php';

try
{
	$processor = new Processor();
	$processor->processOne();
}
catch (Exception $e)
{
	var_dump($e);
}
