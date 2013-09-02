<?php
class Cache
{
	private $bypassCache;

	public function bypass($doBypass)
	{
		$this->bypassCache = $doBypass;
	}

	public function isBypassed()
	{
		return $this->bypassCache;
	}

	private function urlToPath($url)
	{
		$name = md5($url) . sha1($url);
		return Config::$cachePath . DIRECTORY_SEPARATOR . $name;
	}

	public function load($url)
	{
		$path = $this->urlToPath($url);
		$data = file_get_contents($path);
		$pos = strpos($data, "\n\n");
		$headers = unserialize(substr($data, 0, $pos));
		$contents = substr($data, $pos + 2);
		foreach ($headers as $key => $value)
		{
			HttpHeadersHelper::setCurrentHeader($key, $value);
		}
		echo $contents;
	}

	public function isFresh($url)
	{
		$path = $this->urlToPath($url);
		if ($this->isBypassed())
		{
			return false;
		}
		if (!Config::$cacheEnabled)
		{
			return false;
		}
		if (!file_exists($path))
		{
			return false;
		}
		if (time() - filemtime($path) > Config::$cacheTimeToLive)
		{
			return false;
		}
		return true;
	}

	public function save($url, $renderFunction)
	{
		$path = $this->urlToPath($url);
		ob_start();

		$renderFunction();
		flush();

		$headers = HttpHeadersHelper::getCurrentHeaders();
		$contents = ob_get_contents();
		ob_end_clean();

		$handle = fopen($path, 'wb');
		flock($handle, LOCK_EX);
		fwrite($handle, serialize($headers));
		fwrite($handle, "\n\n");
		fwrite($handle, $contents);
		fclose($handle);

		echo $contents;
	}
}
