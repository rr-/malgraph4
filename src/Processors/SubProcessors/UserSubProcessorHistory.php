<?php
class UserSubProcessorHistory extends UserSubProcessor
{
	const URL_HISTORY = 0;

	public function getURLs($userName)
	{
		return
		[
			self::URL_HISTORY => 'http://myanimelist.net/history/' . $userName,
		];
	}

	public function process(array $documents, &$context)
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
}
