<?php
abstract class AbstractProcessor
{
	public abstract function getSubProcessors();

	public function beforeProcessing(&$context)
	{
	}

	public function afterProcessing(&$context)
	{
	}

	public function process($key)
	{
		if (empty($key))
		{
			return;
		}

		$urls = [];
		try
		{
			$context = new ProcessingContext();
			$context->key = $key;

			$subProcessors = $this->getSubProcessors();
			$urlMap = [];
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

			$documents = Downloader::downloadMulti($urls);

			foreach ($documents as &$document)
			{
				//別ハックは、	Another hack
				//私は静かに	makes me
				//泣きます		quietly weep
				$document->content = '<?xml encoding="utf-8" ?'.'>' . $document->content;
			}

			$f = function() use ($subProcessors, $context, $urlMap, $documents)
			{
				$this->beforeProcessing($context);
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
				$this->afterProcessing($context);
			};

			R::transaction($f);
		}
		catch (Exception $e)
		{
			if (isset($documents))
			{
				$e->documents = $documents;
			}
			Downloader::purgeCache($urls);
			throw $e;
		}

		return $context;
	}
}
