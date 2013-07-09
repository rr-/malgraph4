<?php
class UserSubProcessorLists extends UserSubProcessor
{
	const URL_ANIMELIST = 0;
	const URL_MANGALIST = 1;
	const URL_ANIMEINFO = 2;
	const URL_MANGAINFO = 3;

	public function getURLs($userName)
	{
		return
		[
			self::URL_ANIMELIST => 'http://myanimelist.net/animelist/' . $userName,
			self::URL_MANGALIST => 'http://myanimelist.net/mangalist/' . $userName,
			self::URL_ANIMEINFO => 'http://myanimelist.net/malappinfo.php?u=' . $userName . '&status=all&type=anime',
			self::URL_MANGAINFO => 'http://myanimelist.net/malappinfo.php?u=' . $userName . '&status=all&type=manga',
		];
	}

	public function process(array $documents, &$context)
	{
		foreach (Media::getConstList() as $media)
		{
			$key = $media == Media::Anime
				? self::URL_ANIMELIST
				: self::URL_MANGALIST;
			$isPrivate = strpos($documents[$key]->content, 'This list has been made private by the owner') !== false;

			$key = $media == Media::Anime
				? self::URL_ANIMEINFO
				: self::URL_MANGAINFO;
			$doc = self::getDOM($documents[$key]);
			$xpath = new DOMXPath($doc);
			if ($xpath->query('//myinfo')->length == 0)
			{
				throw new BadDocumentNodeException($documents[$key], 'myinfo');
			}

			$nodes = $xpath->query('//anime | //manga');
			foreach ($nodes as $root)
			{
				$id         = Strings::makeInteger(self::getNodeValue($xpath, 'series_animedb_id | series_mangadb_id', $root));
				$score      = Strings::makeInteger(self::getNodeValue($xpath, 'my_score', $root));
				$startDate  = Strings::makeDate(self::getNodeValue($xpath, 'my_start_date', $root));
				$finishDate = Strings::makeDate(self::getNodeValue($xpath, 'my_finish_date', $root));
				$status     = Strings::makeEnum(self::getNodeValue($xpath, 'my_status', $root), [
					1 => UserListStatus::Completing,
					2 => UserListStatus::Finished,
					3 => UserListStatus::OnHold,
					4 => UserListStatus::Dropped,
					6 => UserListStatus::Planned
				], UserListStatus::Unknown);

				if ($media == Media::Anime)
				{
					$finishedEpisodes = Strings::makeInteger(self::getNodeValue($xpath, 'my_watched_episodes', $root));
				}
				else
				{
					$finishedChapters = Strings::makeInteger(self::getNodeValue($xpath, 'my_read_chapters', $root));
					$finishedVolumes  = Strings::makeInteger(self::getNodeValue($xpath, 'my_read_volumes', $root));
				}
			}

			$timeSpent = Strings::makeFloat(self::getNodeValue($xpath, '//user_days_spent_watching'));
		}
	}
}
