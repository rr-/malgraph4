<?php
abstract class AbstractController
{
	public static function parseRequest($url, &$controllerContext)
	{
		throw new UnimplementedException();
	}

	public static function getViewName()
	{
		throw new UnimplementedException();
	}

	public function work($controllerContext)
	{
		$viewContext = new ViewContext();
		$this->doWork($controllerContext, $viewContext);
		View::render($this->getViewName(), $viewContext);
	}

	public function doWork($controllerContext, &$viewContext)
	{
		throw new UnimplementedException();
	}
}
