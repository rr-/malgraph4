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

		ob_start();
		try
		{
			include $path;
			$html = ob_get_contents();
		}
		finally
		{
			ob_end_clean();
		}

		$output = '';
		$open = 0;
		$i = 0;
		while ($i < strlen($html))
		{
			$c = $html{$i};
			if ($c == '<')
			{
				//process tag
				$tag = '';
				$j = strpos($html, '>', $i);
				if ($j === false)
				{
					throw new Exception('Unclosed tag');
				}
				$tag = substr($html, $i, $j + 1 - $i);
				$i = $j + 1;

				$tag = str_replace(["\t", "\r", "\n"], ' ', $tag);
				$j = strpos($tag, '  ');
				while ($j !== false)
				{
					$tag = substr_replace($tag, '', $j, 1);
					$j = strpos($tag, '  ', $j);
				}

				$tag = str_replace(' />', '/>', $tag);
				$tag = str_replace(' >', '>', $tag);
				$output .= $tag;

				$open += ((strncmp($tag, '<pre', 4) == 0) or (strncmp($tag, '<textarea', 9) == 0));
				$open -= ((strncmp($tag, '</pre', 5) == 0) or (strncmp($tag, '</textarea', 10) == 0));
				continue;
			}
			else
			{
				//process space between tags
				$j = strpos($html, '<', $i);
				if ($j === false)
				{
					$j = strlen($html);
				}
				$text = substr($html, $i, $j - $i);
				$i = $j;

				if ($open > 0)
				{
					$output .= $text;
					continue;
				}
				$text = str_replace(["\t", "\r", "\n"], '', $text);

				$j = strpos($text, '  ');
				while ($j !== false)
				{
					$text = substr_replace($text, '', $j, 1);
					$j = strpos($text, '  ', $j);
				}

				$output .= $text;
			}
		}
		echo $output;
	}
}
