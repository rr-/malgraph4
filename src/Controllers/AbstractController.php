<?php
abstract class AbstractController
{
	public static function parseRequest($url, &$controllerContext)
	{
		throw new UnimplementedException();
	}

	public static function work($controllerContext, &$viewContext)
	{
		throw new UnimplementedException();
	}
}
