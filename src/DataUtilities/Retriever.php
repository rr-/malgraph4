<?php
class Retriever
{
	public static function getUserMediaList($userId, $media)
	{
		$pdo = Database::getPDO();
		$stmt = $pdo->prepare('SELECT ' .
			'm.*, uml.* ' .
			'FROM user_media_list uml ' .
			'LEFT JOIN media m ON uml.mal_id = m.mal_id ' .
			'AND uml.media = m.media ' .
			'WHERE uml.user_id = ? AND uml.media = ?');
		$stmt->execute([$userId, $media]);
		return $stmt->fetchAll();
	}

	public static function getUser($userId)
	{
		$pdo = Database::getPDO();
		$stmt = $pdo->prepare('SELECT * FROM users WHERE user_id = ?');
		$stmt->execute([$userId]);
		return $stmt->fetch();
	}

	public static function getUserClubs($userId)
	{
		$pdo = Database::getPDO();
		$stmt = $pdo->prepare('SELECT * FROM user_clubs WHERE user_id = ?');
		$stmt->execute([$userId]);
		return $stmt->fetchAll();
	}

	public static function getUserFriends($userId)
	{
		$pdo = Database::getPDO();
		$stmt = $pdo->prepare('SELECT * FROM user_friends WHERE user_id = ?');
		$stmt->execute([$userId]);
		return $stmt->fetchAll();
	}

	public static function isUserMediaListPrivate($userId, $media)
	{
		$pdo = Database::getPDO();
		$stmt = $pdo->prepare('SELECT ' .
			Media::toString($media) . '_private AS private ' .
			'FROM users WHERE user_id = ?');
		$stmt->execute([$userId]);
		return $stmt->fetch()->private;
	}

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

	public static function getMediaTitleText($row)
	{
		if (!empty($row->title))
		{
			return $row->title;
		}
		return 'Unknown ' . Media::toString($row->media) . ' entry #' . $row->mal_id;
	}

	public static function getMeanScore(array $rows)
	{
		$sum = 0;
		$count = 0;
		foreach ($rows as $row)
		{
			if ($row->score)
			{
				$sum += $row->score;
				$count ++;
			}
		}
		return $sum / max(1, $count);
	}

	public static function getTotalDuration($row)
	{
		if ($row->media == Media::Anime)
		{
			return $row->episodes * $row->duration;
		}
		elseif ($row->media == Media::Manga)
		{
			return $row->chapters * 10;
		}
	}

	public static function getCompletedDuration($row)
	{
		if ($row->media == Media::Anime)
		{
			return $row->finished_episodes * $row->duration;
		}
		elseif ($row->media == Media::Manga)
		{
			return $row->finished_chapters * 10;
		}
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
