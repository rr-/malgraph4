<?php
require_once 'config.php';

class Downloader
{
	private function prepareHandle($url)
	{
		$handle = curl_init();
		curl_setopt($handle, CURLOPT_URL, $url);
		curl_setopt($handle, CURLOPT_HEADER, 1);
		curl_setopt($handle, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($handle, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
		curl_setopt($handle, CURLOPT_ENCODING, '');
		return $handle;
	}

	public function downloadMulti(array $urls)
	{
		$handles = [];
		$results = [];

		$multiHandle = curl_multi_init();
		foreach ($urls as $i => $url)
		{
			$handle = $this->prepareHandle($url);
			curl_multi_add_handle($multiHandle, $handle);
			$handles[$i] = $handle;
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

		foreach ($handles as $i => $handle)
		{
			$results[$i] = curl_multi_getcontent($handle);
			curl_multi_remove_handle($multiHandle, $handle);
		}

		curl_multi_close($multiHandle);

		return $results;
	}
}
