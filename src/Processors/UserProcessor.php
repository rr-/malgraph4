<?php
class UserProcessor implements AbstractProcessor
{
	public function process($userName)
	{
		if (empty($userName))
		{
			return;
		}

		$processors = [];
		$processors []= new UserSubProcessorProfile();
		$processors []= new UserSubProcessorClubs();
		$processors []= new UserSubProcessorFriends();
		$processors []= new UserSubProcessorHistory();
		$processors []= new UserSubProcessorLists();

		$urls = [];
		foreach ($processors as $processor)
		{
			$urls[get_class($processor)] = $processor->getURLs($userName);
		}

		$downloader = new Downloader();
		$documents = $downloader->downloadMulti($urls);

		foreach ($processors as $processor)
		{
			$processor->process($documents[get_class($processor)]);
		}
	}
}
