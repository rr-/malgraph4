<?php
require_once 'downloader.php';
require_once 'processor.php';
require_once 'strings.php';

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

	private static function getNodeValue(DOMDocument $doc, $query, $attrib = null)
	{
		$xpath = new DOMXPath($doc);
		$node = $xpath->query($query)->item(0);
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

		$userName = Strings::removeSpaces(self::getNodeValue($doc, '//title'));
		$userName = substr($userName, 0, strpos($userName, '\'s Profile'));
		$profilePictureURL = self::getNodeValue($doc, '//td[@class = \'profile_leftcell\']//img', 'src');
		$joinDate = Strings::makeDate(self::getNodeValue($doc, '//td[text() = \'Join Date\']/following-sibling::td'));
		$malID = Strings::makeInteger(Strings::parseURL(self::getNodeValue($doc, '//a[text() = \'All Comments\']', 'href'))['query']['id']);
		$animeViewCount = Strings::makeInteger(self::getNodeValue($doc, '//td[text() = \'Anime List Views\']/following-sibling::td'));
		$mangaViewCount = Strings::makeInteger(self::getNodeValue($doc, '//td[text() = \'Manga List Views\']/following-sibling::td'));
		$commentCount = Strings::makeInteger(self::getNodeValue($doc, '//td[text() = \'Comments\']/following-sibling::td'));
		$postCount = Strings::makeInteger(self::getNodeValue($doc, '//td[text() = \'Forum Posts\']/following-sibling::td'));
		$birthday = Strings::makeDate(self::getNodeValue($doc, '//td[text() = \'Birthday\']/following-sibling::td'));
		$location = Strings::removespaces(self::getNodeValue($doc, '//td[text() = \'Location\']/following-sibling::td'));
		$website = Strings::removeSpaces(self::getNodeValue($doc, '//td[text() = \'Website\']/following-sibling::td'));
		$gender = self::getNodeValue($doc, '//td[text() = \'Gender\']/following-sibling::td');
		switch($gender)
		{
			case 'Female': $gender = 'F'; break;
			case 'Male': $gender = 'M'; break;
			default: $gender = '?'; break;
		}
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
		$results = $downloader->downloadMulti($urls);

		$this->processProfile($results);
		$this->processClubs($results);
		$this->processFriends($results);
	}
}
