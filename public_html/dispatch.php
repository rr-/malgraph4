<?php
chdir('..');
require_once('src/core.php');

$dir = implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'src', 'Controllers']);
$classNames = ReflectionHelper::loadClasses($dir);
$classNames = array_filter($classNames, function($className) {
	return substr_compare($className, 'Controller', -10, 10) === 0;
});
$bypassCache = !empty($_GET['bypass-cache']);

$controllerContext = new ControllerContext();
$viewContext = new ViewContext();
try
{
	$url = $_SERVER['REQUEST_URI'];
	$workingClassName = null;
	foreach ($classNames as $className)
	{
		if ($className::parseRequest($url, $controllerContext))
		{
			$workingClassName = $className;
			break;
		}
	}

	if (!empty($workingClassName))
	{
		if (Cache::isFresh($url) and !$bypassCache and !$controllerContext->bypassCache)
		{
			Cache::load($url);
		}
		else
		{
			Cache::beginSave($url);
			$workingClassName::work($controllerContext, $viewContext);
			View::render($viewContext);
			flush();
			Cache::endSave();
		}
	}

	if (HttpHeadersHelper::headersSent())
	{
		if (HttpHeadersHelper::getCurrentHeader('Content-Type') == 'text/html')
		{
			printf('<!-- retrieved in %.05fs -->', microtime(true) - $viewContext->renderStart);
		}
		exit(0);
	}

	$viewContext->viewName = 'error-404';
	View::render($viewContext);
}
catch (Exception $e)
{
	#log error information
	$viewContext->viewName = 'error';
	$viewContext->exception = $e;
	Logger::log(Config::$errorLogPath, $e);
	View::render($viewContext);
}
exit(1);
