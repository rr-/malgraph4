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
	foreach ($classNames as $className)
	{
		if ($className::parseRequest($url, $controllerContext))
		{
			$class = new $className();
			$class->work($controllerContext);
			exit(0);
		}
	}
	$viewContext = new ViewContext();
	View::render('error-404', $viewContext);
}
catch (Exception $e)
{
	#log error information
	$viewContext = new ViewContext();
	$viewContext->exception = $e;
	View::render('error', $viewContext);
}
