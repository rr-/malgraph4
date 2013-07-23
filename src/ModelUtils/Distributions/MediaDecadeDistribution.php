<?php
class MediaDecadeDistribution extends AbstractDistribution
{
	protected function finalize()
	{
		if (!empty($this->keys))
		{
			$min = $max = reset($this->keys);
			while (list($i,) = each($this->keys))
			{
				if ($min > $i)
				{
					$min = $i;
				}
				elseif ($max < $i)
				{
					$max = $i;
				}
			}
			for ($i = $min + 10; $i < $max; $i += 10)
			{
				$this->addGroup($i);
			}
		}

		krsort($this->groups, SORT_NUMERIC);
		krsort($this->entries, SORT_NUMERIC);
	}

	public function getNullGroupKey()
	{
		return 0;
	}

	public static function getPublishedDecade($entry)
	{
		$year = MediaYearDistribution::getPublishedYear($entry);
		$decade = floor($year / 10) * 10;
		return $decade;
	}

	protected function addEntry($entry)
	{
		$this->addToGroup(self::getPublishedDecade($entry), $entry);
	}
}
