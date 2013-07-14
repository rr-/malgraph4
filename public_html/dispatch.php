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
	return !$isAbstract and strpos($className, 'Controller') !== false;
});

try
{
	$request = $_SERVER['REQUEST_URI'];
	foreach ($classNames as $className)
	{
		if ($className::match($request))
		{
			$class = new $className();
			$class->work($request);
			exit(0);
		}
	}
	throw new Exception('Bad URL');
}
catch (Exception $e)
{
	#todo:
	#better error handler
	echo '<pre>';
	var_dump($e);
	echo '</pre>';
}
