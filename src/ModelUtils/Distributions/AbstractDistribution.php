<?php
abstract class AbstractDistribution
{
	const IGNORE_NULL_KEY = 1;
	const IGNORE_EMPTY_GROUPS = 2;

	protected $groups = [];
	protected $entries = [];
	protected $keys = [];

	public function __construct(array $entries = [])
	{
		if (!empty($entries))
		{
			foreach ($entries as $entry)
			{
				$this->addEntry($entry);
			}
			$this->finalize();
		}
	}

	public abstract function addEntry($entry);

	protected function sortEntries()
	{
		foreach ($this->entries as $group => $entries)
		{
			uasort($entries, function($a, $b)
			{
				return strcmp($a->title, $b->title);
			});
		}
	}

	protected function sortGroups()
	{
	}

	public function finalize()
	{
		$this->sortEntries();
		$this->sortGroups();
	}

	public function getNullGroupKey()
	{
		return null;
	}

	protected function addGroup($key)
	{
		if (!isset($this->keys[(string)$key]))
		{
			$this->keys[(string)$key] = $key;
			$this->groups[(string)$key] = 0;
			$this->entries[(string)$key] = [];
		}
	}

	public function addToGroup($key, $entry, $weight = 1)
	{
		$this->addGroup($key);
		$this->groups[(string)$key] += $weight;
		$this->entries[(string)$key] []= $entry;
	}

	public function getGroupEntries($key)
	{
		if (!isset($this->entries[(string)$key]))
		{
			return null;
		}
		return $this->entries[(string)$key];
	}

	public function getGroupSize($key)
	{
		if (!isset($this->groups[(string)$key]))
		{
			return null;
		}
		return $this->groups[(string)$key];
	}



	public function getGroupsKeys($flags = 0)
	{
		$x = $this->keys;
		if ($flags & self::IGNORE_NULL_KEY)
		{
			unset($x[(string)$this->getNullGroupKey()]);
		}
		if ($flags & self::IGNORE_EMPTY_GROUPS)
		{
			$x = array_filter($x, function($key)
					{ return $this->getGroupSize($key) > 0; });
		}
		$x = array_values($x);
		return $x;
	}

	public function getGroupsEntries($flags = 0)
	{
		$keys = $this->getGroupsKeys($flags);
		$x = [];
		foreach ($keys as $key)
		{
			$x[(string) $key] = $this->getGroupEntries($key);
		}
		return $x;
	}

	public function getAllEntries($flags = 0)
	{
		$groups = self::getGroupsEntries($flags);
		if ($groups === null)
		{
			return null;
		}
		$x = [];
		foreach ($groups as $key => $entries)
		{
			foreach ($entries as $entry)
			{
				$x[$entry->getID()] = $entry;
			}
		}
		return $x;
	}

	public function getGroupsSizes($flags = 0)
	{
		$keys = $this->getGroupsKeys($flags);
		$x = [];
		foreach ($keys as $key)
		{
			$x[(string) $key] = $this->getGroupSize($key);
		}
		$x = array_values($x);
		return $x;
	}

	public function getLargestGroupSize($flags = 0)
	{
		$x = $this->getGroupsSizes($flags);
		if (empty($x))
		{
			return 0;
		}
		return max($x);
	}

	public function getLargestGroupKey($flags = 0)
	{
		return array_search($this->getLargestGroupSize($flags), $this->groups);
	}

	public function getSmallestGroupSize($flags = 0)
	{
		$x = $this->getGroupsSizes($flags);
		return min($x);
	}

	public function getSmallestGroupKey($flags = 0)
	{
		return array_search($this->getSmallestGroupSize($flags), $this->groups);
	}

	public function getTotalSize($flags = 0)
	{
		return array_sum($this->getGroupsSizes($flags));
	}
}
