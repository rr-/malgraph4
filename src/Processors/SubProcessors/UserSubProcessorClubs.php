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
		$doc = self::getDOM($documents[self::URL_CLUBS]);
		$xpath = new DOMXPath($doc);

		$data = [];
		foreach ($xpath->query('//ol/li/a[contains(@href, \'/club\')]') as $node)
		{
			$url = Strings::parseURL($node->getAttribute('href'));
			$clubMalId = Strings::makeInteger($url['query']['cid']);
			$clubName = Strings::removeSpaces($node->nodeValue);
			$data []= [
				'user_id' => $context->userId,
				'mal_id' => $clubMalId,
				'name' => $clubName
			];
		}
		$this->insert('user_clubs', $data);
	}
}
