<?php
require_once 'src/processor.php';

try
{
	$processor = new Processor();
	$processor->processOne();
}
catch (Exception $e)
{
	#todo:
	#better error handling
	var_dump($e);
}
