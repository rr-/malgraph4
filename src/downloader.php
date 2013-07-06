<?php
require_once 'config.php';

class Downloader
{
	private static function prepareHandle($url)
	{
		$handle = curl_init();
		curl_setopt($handle, CURLOPT_URL, $url);
		curl_setopt($handle, CURLOPT_HEADER, 1);
		curl_setopt($handle, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($handle, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
		curl_setopt($handle, CURLOPT_ENCODING, '');
		return $handle;
	}

	private static function parseResult($result)
	{
		$pos = strpos($result, "\r\n\r\n");
		$headers = [];
		$content = substr($result, $pos + 4);
		$headerLines = explode("\r\n", substr($result, 0, $pos));

		preg_match('/\d{3}/', array_shift($headerLines), $matches);
		$code = intval($matches[0]);

		foreach ($headerLines as $line)
		{
			list($key, $value) = explode(': ', $line);
			if (!isset($headers[$key]))
			{
				$headers[$key] = $value;
			}
			else
			{
				$headers[$key] = array_merge(
					array($headers[$key]),
					array($value));
			}
		}

		//別ハックは、	Another hack
		//私は静かに	makes me
		//泣きます		quietly weep
		$content = '<?xml encoding="utf-8" ?'.'>' . $content;

		$cls = new StdClass();
		$cls->code = $code;
		$cls->headers = $headers;
		$cls->content = $content;
		return $cls;
	}

	public function downloadMulti(array $urls)
	{
		$handles = [];
		$results = [];

		$mirrorPaths = [];
		if (Config::$mirrorEnabled)
		{
			foreach ($urls + [] as $key => $url)
			{
				$path = Config::$mirrorPath . DIRECTORY_SEPARATOR . rawurlencode($url) . '.dat';
				$mirrorPaths[$key] = $path;
				if (file_exists($path))
				{
					$rawResult = file_get_contents($path);
					$results[$key] = self::parseResult($rawResult);
					unset($urls[$key]);
				}
			}
		}

		$multiHandle = curl_multi_init();
		foreach ($urls as $key => $url)
		{
			$handle = self::prepareHandle($url);
			curl_multi_add_handle($multiHandle, $handle);
			$handles[$key] = $handle;
		}

		$running = null;
		do
		{
			$status = curl_multi_exec($multiHandle, $running);
		}
		while ($status == CURLM_CALL_MULTI_PERFORM);

		while ($running and $status == CURLM_OK)
		{
			if (curl_multi_select($multiHandle) != -1)
			{
				do
				{
					$status = curl_multi_exec($multiHandle, $running);
				}
				while ($status == CURLM_CALL_MULTI_PERFORM);
			}
		}

		foreach ($handles as $key => $handle)
		{
			$rawResult = curl_multi_getcontent($handle);
			if (Config::$mirrorEnabled)
			{
				file_put_contents($mirrorPaths[$key], $rawResult);
			}
			$results[$key] = self::parseResult($rawResult);
			curl_multi_remove_handle($multiHandle, $handle);
		}

		curl_multi_close($multiHandle);

		return $results;
	}
}
