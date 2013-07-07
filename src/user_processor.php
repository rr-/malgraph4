<?php
require_once 'downloader.php';
require_once 'processor.php';
require_once 'strings.php';
require_once 'enums.php';

class UserProcessor implements Processor
{
	const URL_PROFILE = 1;
	const URL_ANIMELIST = 2;
	const URL_MANGALIST = 3;
	const URL_ANIMEINFO = 4;
	const URL_MANGAINFO = 5;
	const URL_HISTORY = 6;
	const URL_FRIENDS = 7;
	const URL_CLUBS = 8;

	private static function getNodeValue(DOMXPath $xpath, $query, DOMNode $parentNode = null, $attrib = null)
	{
		$node = $xpath->query($query, $parentNode)->item(0);
		if (!empty($node))
		{
			return $attrib
				? $node->getAttribute($attrib)
				: $node->nodeValue;
		}
		return null;
	}

	private static function getDOM($document)
	{
		$doc = new DOMDocument;
		$doc->preserveWhiteSpace = false;
		ErrorHandler::suppress();
		$doc->loadHTML($document->content);
		ErrorHandler::restore();
		return $doc;
	}

	private function processProfile(array $documents)
	{
		$doc = self::getDOM($documents[self::URL_PROFILE]);
		$xpath = new DOMXPath($doc);

		$userName       = Strings::removeSpaces(self::getNodeValue($xpath, '//title'));
		$userName       = substr($userName, 0, strpos($userName, '\'s Profile'));
		$pictureURL     = self::getNodeValue($xpath, '//td[@class = \'profile_leftcell\']//img', null, 'src');
		$joinDate       = Strings::makeDate(self::getNodeValue($xpath, '//td[text() = \'Join Date\']/following-sibling::td'));
		$malID          = Strings::makeInteger(Strings::parseURL(self::getNodeValue($xpath, '//a[text() = \'All Comments\']', null, 'href'))['query']['id']);
		$animeViewCount = Strings::makeInteger(self::getNodeValue($xpath, '//td[text() = \'Anime List Views\']/following-sibling::td'));
		$mangaViewCount = Strings::makeInteger(self::getNodeValue($xpath, '//td[text() = \'Manga List Views\']/following-sibling::td'));
		$commentCount   = Strings::makeInteger(self::getNodeValue($xpath, '//td[text() = \'Comments\']/following-sibling::td'));
		$postCount      = Strings::makeInteger(self::getNodeValue($xpath, '//td[text() = \'Forum Posts\']/following-sibling::td'));
		$birthday       = Strings::makeDate(self::getNodeValue($xpath, '//td[text() = \'Birthday\']/following-sibling::td'));
		$location       = Strings::removespaces(self::getNodeValue($xpath, '//td[text() = \'Location\']/following-sibling::td'));
		$website        = Strings::removeSpaces(self::getNodeValue($xpath, '//td[text() = \'Website\']/following-sibling::td'));
		$gender         = Strings::makeEnum(self::getNodeValue($xpath, '//td[text() = \'Gender\']/following-sibling::td'), ['Female' => UserGender::Female, 'Male' => UserGender::Male], UserGender::Unknown);
	}

	private function processClubs(array $documents)
	{
		$doc = self::getDOM($documents[self::URL_CLUBS]);
		$xpath = new DOMXPath($doc);
		foreach ($xpath->query('//ol/li/a[contains(@href, \'/club\')]') as $node)
		{
			$url = Strings::parseURL($node->getAttribute('href'));
			$clubID = Strings::makeInteger($url['query']['cid']);
			$clubName = Strings::removeSpaces($node->nodeValue);
		}
	}

	private function processFriends(array $documents)
	{
		$doc = self::getDOM($documents[self::URL_FRIENDS]);
		$doc->preserveWhiteSpace = false;
		$xpath = new DOMXPath($doc);
		foreach ($xpath->query('//a[contains(@href, \'profile\')]/strong') as $node)
		{
			$friendName = Strings::removeSpaces($node->nodeValue);
		}
	}

	private function processLists(array $documents)
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
				throw new Exception('Expected myinfo block not found in ' . $documents[$key]->url);
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

	private function processHistory(array $documents)
	{
		$doc = self::getDOM($documents[self::URL_HISTORY]);
		$xpath = new DOMXPath($doc);

		$nodes = $xpath->query('//table//td[@class = \'borderClass\']/..');
		foreach ($nodes as $node)
		{
			//basic info
			$link = $node->childNodes->item(0)->childNodes->item(0)->getAttribute('href');
			preg_match('/(\d+)\/?$/', $link, $matches);
			$id = intval($matches[0]);
			if (strpos($link, 'manga') !== false)
			{
				$chapter = intval($node->childNodes->item(0)->childNodes->item(2)->nodeValue);
				$type = Media::Manga;
			}
			else
			{
				$episode = intval($node->childNodes->item(0)->childNodes->item(2)->nodeValue);
				$type = Media::Anime;
			}

			//parse time
			//That's what MAL servers output for MG client
			if (isset($documents[self::URL_HISTORY]->headers['Date']))
			{
				date_default_timezone_set('UTC');
				$now = strtotime($documents[self::URL_HISTORY]->headers['Date']);
			}
			else
			{
				$now = time();
			}
			date_default_timezone_set('America/Los_Angeles');
			$hour =   date('H', $now);
			$minute = date('i', $now);
			$second = date('s', $now);
			$day =    date('d', $now);
			$month =  date('m', $now);
			$year =   date('Y', $now);
			$dateString = $node->childNodes->item(2)->nodeValue;
			if (preg_match('/(\d*) seconds? ago/', $dateString, $matches))
			{
				$second -= intval($matches[1]);
			}
			elseif (preg_match('/(\d*) minutes? ago/', $dateString, $matches))
			{
				$second += - intval($matches[1]) * 60;
			}
			elseif (preg_match('/(\d*) hours? ago/', $dateString, $matches))
			{
				$minute += - intval($matches[1]) * 60;
			}
			elseif (preg_match('/Today, (\d*):(\d\d) (AM|PM)/', $dateString, $matches))
			{
				$hour = intval($matches[1]);
				$minute = intval($matches[2]);
				$hour += ($matches[3] == 'PM' and $hour != 12) ? 12 : 0;
			}
			elseif (preg_match('/Yesterday, (\d*):(\d\d) (AM|PM)/', $dateString, $matches))
			{
				$hour = intval($matches[1]);
				$minute = intval($matches[2]);
				$hour += ($matches[3] == 'PM' and $hour != 12) ? 12 : 0;
				$hour -= 24;
			}
			elseif (preg_match('/(\d\d)-(\d\d)-(\d\d), (\d*):(\d\d) (AM|PM)/', $dateString, $matches))
			{
				$year = intval($matches[3]) + 2000;
				$month = intval($matches[1]);
				$day = intval($matches[2]);
				$hour = intval($matches[4]);
				$minute = intval($matches[5]);
				$hour += ($matches[6] == 'PM' and $hour != 12) ? 12 : 0;
			}
			$timestamp = mktime($hour, $minute, $second, $month, $day, $year);
			date_default_timezone_set('UTC');
		}
	}

	public function process($userName)
	{
		if (empty($userName))
		{
			return;
		}

		$urls =
		[
			self::URL_PROFILE => 'http://myanimelist.net/profile/' . $userName,
			self::URL_ANIMELIST => 'http://myanimelist.net/animelist/' . $userName,
			self::URL_MANGALIST => 'http://myanimelist.net/mangalist/' . $userName,
			self::URL_ANIMEINFO => 'http://myanimelist.net/malappinfo.php?u=' . $userName . '&status=all&type=anime',
			self::URL_MANGAINFO => 'http://myanimelist.net/malappinfo.php?u=' . $userName . '&status=all&type=manga',
			self::URL_HISTORY => 'http://myanimelist.net/history/' . $userName,
			self::URL_CLUBS => 'http://myanimelist.net/profile/' . $userName . '/clubs',
			self::URL_FRIENDS => 'http://myanimelist.net/profile/' . $userName . '/friends',
		];
		$downloader = new Downloader();
		$documents = $downloader->downloadMulti($urls);

		$this->processProfile($documents);
		$this->processClubs($documents);
		$this->processFriends($documents);
		$this->processLists($documents);
		$this->processHistory($documents);
	}
}
