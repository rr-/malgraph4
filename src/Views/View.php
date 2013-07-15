<?php
class View
{
	protected static $viewContext;

	public static function render($viewContext)
	{
		ob_start();
		$ret = null;
		try
		{
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
		self::renderFile(self::$viewContext->name, self::$viewContext);
	}

	public static function renderFile($name, $viewContext)
	{
		$path = __DIR__ . DIRECTORY_SEPARATOR . $name . '.phtml';
		include $path;
	}
}
