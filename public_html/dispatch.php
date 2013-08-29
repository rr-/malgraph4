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

if (!empty(Config::$maintenanceMessage))
{
	$viewContext->viewName = 'maintenance';
	$viewContext->layoutName = 'layout-headerless';
	View::render($viewContext);
}
elseif (isset($_GET['e']))
{
	try
	{
		$viewContext->viewName = 'error-' . $_GET['e'];
		View::render($viewContext);
	}
	catch (Exception $e)
	{
		$viewContext->viewName = 'error-404';
		View::render($viewContext);
	}
}
else
{
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
			$workingClassName::preWork($controllerContext, $viewContext);
			$bypassCache |= $controllerContext->bypassCache;
			if (Cache::isFresh($url) and !$bypassCache)
			{
				Cache::load($url);
				flush();
			}
			else
			{
				if (!$bypassCache)
				{
					Cache::beginSave($url);
				}
				$workingClassName::work($controllerContext, $viewContext);
				View::render($viewContext);
				if (!$bypassCache)
				{
					flush();
					Cache::endSave();
				}
			}
			$workingClassName::postWork($controllerContext, $viewContext);

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
}
