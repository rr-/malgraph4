<?php
class UserSubProcessorClubs extends UserSubProcessor
{
	const URL_CLUBS = 0;

	public function getURLs($userName)
	{
		return
		[
			self::URL_CLUBS => 'http://myanimelist.net/profile/' . $userName . '/clubs',
		];
	}

	public function process(array $documents, &$context)
	{
		$pdo = Database::getPDO();

		$doc = self::getDOM($documents[self::URL_CLUBS]);
		$stmt = $pdo->prepare('INSERT INTO user_clubs(user_id, club_id, club_name) VALUES(?, ?, ?)');
		$xpath = new DOMXPath($doc);
		foreach ($xpath->query('//ol/li/a[contains(@href, \'/club\')]') as $node)
		{
			$url = Strings::parseURL($node->getAttribute('href'));
			$clubId = Strings::makeInteger($url['query']['cid']);
			$clubName = Strings::removeSpaces($node->nodeValue);
			$stmt->execute([$context->userId, $clubId, $clubName]);
		}
	}
}
