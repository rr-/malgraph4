<?php
abstract class AbstractDistribution
{
	const IGNORE_NULL_KEY = 1;
	const IGNORE_EMPTY_GROUPS = 2;

	protected $groups = [];
	protected $entries = [];

	protected function __construct()
	{
	}

	public static function fromArray(array $arrayDist)
	{
		$dist = new static();
		foreach ($arrayDist as $key => $count)
		{
			$dist->groups[$key] = intval($count);
		}
		$dist->finalize();
		return $dist;
	}

	public static function fromEntries(array $entries = [])
	{
		$dist = new static();
		foreach ($entries as $entry)
		{
			$dist->addEntry($entry);
		}
		$dist->finalize();
		return $dist;
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
		if (!isset($this->groups[$key]))
		{
			$this->groups[$key] = 0;
			$this->entries[$key] = [];
		}
	}

	public function addToGroup($key, $entry, $weight = 1)
	{
		$this->addGroup($key);
		$this->groups[$key] += $weight;
		$this->entries[$key] []= $entry;
	}

	public function getGroupEntries($key)
	{
		if (!isset($this->entries[$key]))
		{
			return null;
		}
		return $this->entries[$key];
	}

	public function getGroupSize($key)
	{
		if (!isset($this->groups[$key]))
		{
			return null;
		}
		return $this->groups[$key];
	}



	public function getGroupsKeys($flags = 0)
	{
		$x = array_combine(array_keys($this->groups), array_keys($this->groups));
		if ($flags & self::IGNORE_NULL_KEY)
		{
			unset($x[$this->getNullGroupKey()]);
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
			$x[$key] = $this->getGroupEntries($key);
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
			$x[$key] = $this->getGroupSize($key);
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
