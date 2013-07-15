<?php
abstract class AbstractProcessor
{
	public abstract function getSubProcessors();

	public function beforeProcessing($context)
	{
		$pdo = Database::getPDO();
		$pdo->exec('BEGIN TRANSACTION');
	}

	public function afterProcessing($context)
	{
		$pdo = Database::getPDO();
		if (!empty($context->exception))
		{
			$pdo->exec('ROLLBACK TRANSACTION');
			return;
		}
		$pdo->exec('COMMIT TRANSACTION');
	}

	public function process($key)
	{
		if (empty($key))
		{
			return;
		}

		$context = new ProcessingContext();
		$this->beforeProcessing($context);

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

		try
		{
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
				$subProcessor->process($subDocuments, $context);
			}
		}
		catch (Exception $e)
		{
			$context->exception = $e;
		}

		$this->afterProcessing($context);

		if (!empty($context->exception))
		{
			throw $e;
		}

		return $context;
	}
}
