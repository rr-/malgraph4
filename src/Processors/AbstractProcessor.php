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
		$urks = [];
		foreach ($subProcessors as $processor)
		{
			foreach ($processor->getURLs($key) as $url)
			{
				if (!isset($urlMap[$url]))
				{
					$urlMap[$url] = [];
				}
				$urlMap[$url] []= $processor;
				$urls[$url] = $url;
			}
		}

		$downloader = new Downloader();
		$documents = $downloader->downloadMulti($urls);

		foreach ($subProcessors as $subProcessor)
		{
			$subDocuments = [];
			foreach ($urlMap as $url => $urlProcessors)
			{
				if (in_array($subProcessor, $urlProcessors))
				{
					$subDocuments []= $documents[$url];
				}
			}
			$subProcessor->process($subDocuments);
		}

		$this->afterProcessing();
	}
}
