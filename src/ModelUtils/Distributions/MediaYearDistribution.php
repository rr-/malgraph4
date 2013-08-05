<?php
class MediaYearDistribution extends AbstractDistribution
{
	protected function finalize()
	{
		/*if (!empty($this->keys))
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
			for ($i = $min + 1; $i < $max; $i ++)
			{
				$this->addGroup($i);
			}
		}*/

		krsort($this->groups, SORT_NUMERIC);
		krsort($this->entries, SORT_NUMERIC);
	}

	public function getNullGroupKey()
	{
		return 0;
	}

	public static function getPublishedYear($entry)
	{
		$season = $entry->getSeason();
		$pos = strpos($season, ' ');
		$ret = $pos !== false
			? substr($season, $pos + 1)
			: $season;
		return intval($ret);
	}

	public function addEntry($entry)
	{
		$this->addToGroup(self::getPublishedYear($entry), $entry);
	}
}
