<?php
class View
{
	protected static $viewName;
	protected static $viewContext;

	public static function render($viewName, $viewContext)
	{
		ob_start();
		$ret = null;
		try
		{
			self::$viewName = $viewName;
			self::$viewContext = $viewContext;
			self::renderFile('layout', $viewContext);
			$ret = ob_get_contents();
		}
		finally
		{
			ob_end_clean();
		}
		echo $ret;
	}

	public static function renderView()
	{
		self::renderFile(self::$viewName, self::$viewContext);
	}

	public static function renderFile($name, $viewContext)
	{
		$path = __DIR__ . DIRECTORY_SEPARATOR . $name . '.phtml';
		include $path;
	}
}
