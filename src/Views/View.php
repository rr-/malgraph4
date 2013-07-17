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
			self::renderFile($viewContext->layoutName, $viewContext);
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
		assert(!empty(self::$viewContext->viewName));
		self::renderFile(self::$viewContext->viewName, self::$viewContext);
	}

	public static function renderFile($name, $viewContext)
	{
		if (empty($name))
		{
			return;
		}
		$path = __DIR__ . DIRECTORY_SEPARATOR . $name . '.phtml';
		include $path;
	}
}
