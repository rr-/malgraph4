<?php
class RatingTimeDistribution extends RatingDistribution
{
	public function addEntry($entry)
	{
		$this->addToGroup($entry->score, $entry, Retriever::getCompletedDuration($entry));
	}

	public function getTotalTime()
	{
		return $this->getTotalSize();
	}
}
