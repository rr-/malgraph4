<?php
abstract class AbstractProcessor
{
	public abstract function getSubProcessors();
	public function beforeProcessing()
	{
	}
	public function afterProcessing()
	{
	}

	public function process($key)
	{
		if (empty($key))
		{
			return;
		}

		$this->beforeProcessing();

		$subProcessors = $this->getSubProcessors();
		$urlMap = [];
		foreach ($subProcessors as $processor)
		{
			foreach ($processor->getURLs($key) as $url)
			{
				$urlMap[$url] = isset($urlMap[$url])
					? array_merge($urlMap[$url], [$processor])
					: [$processor];
			}
		}

		$urls = array_combine(array_keys($urlMap), array_keys($urlMap));
		$downloader = new Downloader();
		$documents = $downloader->downloadMulti($urls);

		foreach ($subProcessors as $processor)
		{
			$processorDocuments = [];
			foreach ($urls as $url)
			{
				if (in_array($processor, $urlMap[$url]))
				{
					$processorDocuments []= $documents[$url];
				}
			}
			$processor->process($processorDocuments);
		}

		$this->afterProcessing();
	}
}
