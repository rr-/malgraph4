<?php
chdir('..');
require_once('src/core.php');

foreach (glob('src/Controllers/*') as $fileName)
{
	include $fileName;
}

$classNames = array_filter(get_declared_classes(), function($className)
{
	$isAbstract = (new ReflectionClass($className))->isAbstract();
	return !$isAbstract and preg_match('/Controller$/', $className);
});

$controllerContext = new ControllerContext();
try
{
	$url = $_SERVER['REQUEST_URI'];
	if (Cache::isFresh($url))
	{
		Cache::load($url);
		exit(0);
	}
	foreach ($classNames as $className)
	{
		if ($className::parseRequest($url, $controllerContext))
		{
			Cache::beginSave($url);
			$viewContext = new ViewContext();
			$className::work($controllerContext, $viewContext);
			View::render($viewContext);
			Cache::endSave();
			exit(0);
		}
	}
	$viewContext = new ViewContext();
	$viewContext->name = 'error-404';
	View::render($viewContext);
}
catch (Exception $e)
{
	#log error information
	$viewContext = new ViewContext();
	$viewContext->name = 'error';
	$viewContext->exception = $e;
	View::render($viewContext);
}
