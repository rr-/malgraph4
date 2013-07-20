<?php
class Retriever
{
	private static function getNumberText($number, $short, $fmt, $shortForm, $longForm)
	{
		$txt = $short ? $shortForm : $longForm;
		if ($number == 0)
		{
			$number = '?';
			$txt .= 's';
		}
		elseif ($number > 1)
		{
			$txt .= 's';
		}
		return sprintf($fmt, $number, $txt);
	}

	public static function getVolumesText($number, $short = false, $fmt = '%s %s')
	{
		return self::getNumberText($number, $short, $fmt, 'vol', 'volume');
	}

	public static function getChaptersText($number, $short = false, $fmt = '%s %s')
	{
		return self::getNumberText($number, $short, $fmt, 'chap', 'chapter');
	}

	public static function getEpisodesText($number, $short = false, $fmt = '%s %s')
	{
		return self::getNumberText($number, $short, $fmt, 'ep', 'episode');
	}

	public static function getTimeSpent(array $rows)
	{
		$sum = 0;
		foreach ($rows as $row)
		{
			$sum += self::getCompletedDuration($row);
		}
		return $sum;
	}

	public static function evaluateDistribution(Distribution $dist) {
		$values = [];
		$allEntries = $dist->getAllEntries();
		$meanScore = self::getMeanScore($allEntries);
		foreach ($dist->getGroupsKeys() as $key) {
			$entry = [];
			$scoreDist = new ScoreDistribution($dist->getGroupEntries($key));
			$localMeanScore = $scoreDist->getRatedCount() * $scoreDist->getMeanScore() + $scoreDist->getUnratedCount() * $meanScore;
			$localMeanScore /= (float)max(1, $dist->getGroupSize($key));
			$weight = $dist->getGroupSize($key) / max(1, $dist->getLargestGroupSize());
			$weight = 1 - pow(1 - pow($weight, 8. / 9.), 2);
			$value = $meanScore + ($localMeanScore - $meanScore) * $weight;
			$values[(string) $key] = $value;
		}
		return $values;
	}

	public static function getMonthPeriod(UserListEntry $entry) {
		$finishedA = explode('-', $entry->getStartDate());
		$finishedB = explode('-', $entry->getFinishDate());
		$yearA = intval($finishedA[0]);
		$yearB = intval($finishedB[0]);
		$monthA = isset($finishedA[1]) ? intval($finishedA[1]) : false;
		$monthB = isset($finishedB[1]) ? intval($finishedB[1]) : false;
		if ($yearB > 1900 and $monthB) {
			$monthPeriod = sprintf('%04d-%02d', $yearB, $monthB);
		} elseif ($yearA > 1900 and $monthA) {
			$monthPeriod = sprintf('%04d-%02d', $yearA, $monthA);
		} else {
			$monthPeriod = '?';
		}
		return $monthPeriod;
	}

	public static function getFranchises(array $entries, $filter = 'default') {
		$all = [];

		$franchises = [];
		$checked = [];
		foreach ($entries as $entry) {
			if (isset($checked[$entry->getID()])) {
				continue;
			}
			$actualFranchise = $entry->getAMEntry()->getFranchise();
			$franchise = null;
			$add  = false;
			//check if any id was set anywhere. sadly, anime relations on mal can be one-way.
			foreach ($actualFranchise->entries as $franchiseEntry) {
				$id = $franchiseEntry->getID();
				if (isset($checked[$id])) {
					$franchise = $checked[$id];
				}
			}
			if ($franchise === null) {
				$franchise = $actualFranchise;
				$franchise->ownEntries = [];
				$add = true;
			}
			foreach ($actualFranchise->entries as $franchiseEntry) {
				$id = $franchiseEntry->getID();
				if (isset($entries[$id])) {
					$franchise->ownEntries[$id] = $entries[$id];
					$checked[$id] = $franchise;
				}
			}
			$franchise->meanScore = UserListService::getMeanScore($franchise->ownEntries);
			if ($add) {
				$franchises []= $franchise;
			}
		}

		//remove groups with less than 2 titles
		if ($filter == 'default') {
			$filter = function($f) { return count($f->ownEntries) > 1; };
		} elseif ($filter === null) {
			$filter = function($f) { return count($f->ownEntries) > 0; };
		}
		if (!empty($filter)) {
			$franchises = array_filter($franchises, $filter);
		}

		uasort($franchises, function($a, $b) { return $b->meanScore > $a->meanScore ? 1 : -1; });
		return $franchises;
	}

	public static function getMismatchedEntries(array $entries) {
		$entriesMismatched = [];
		foreach ($entries as $entry) {
			if ($entry->getType() == AMModel::TYPE_ANIME) {
				$a = $entry->getCompletedEpisodes();
				$b = $entry->getAMEntry()->getEpisodeCount();
			} else {
				$a = $entry->getCompletedChapters();
				$b = $entry->getAMEntry()->getChapterCount();
			}
			if ($a != $b and ($b > 0 or $entry->getAMEntry()->getStatus() == AMEntry::STATUS_PUBLISHING) and $entry->getStatus() == UserListEntry::STATUS_COMPLETED) {
				$entriesMismatched []= $entry;
			}
		}
		return $entriesMismatched;
	}
}
