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
		$doc = $documents[self::URL_CLUBS];
		$dom = self::getDOM($doc);
		$xpath = new DOMXPath($dom);

		Database::delete('userclub', ['user_id' => $context->user->id]);
		$data = [];
		foreach ($xpath->query('//ol/li/a[contains(@href, \'/club\')]') as $node)
		{
			$url = Strings::parseURL($node->getAttribute('href'));
			$clubMalId = Strings::makeInteger($url['query']['cid']);
			$clubName = Strings::removeSpaces($node->nodeValue);
			$data []= [
				'user_id' => $context->user->id,
				'mal_id' => $clubMalId,
				'name' => $clubName
			];
		}
		Database::insert('userclub', $data);
	}
}
