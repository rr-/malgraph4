<?php
class UserSubProcessorFriends extends UserSubProcessor
{
	const URL_FRIENDS = 0;

	public function getURLs($userName)
	{
		return
		[
			self::URL_FRIENDS => 'http://myanimelist.net/profile/' . $userName . '/friends',
		];
	}

	public function process(array $documents, &$context)
	{
		$pdo = Database::getPDO();

		$doc = self::getDOM($documents[self::URL_FRIENDS]);
		$doc->preserveWhiteSpace = false;
		$xpath = new DOMXPath($doc);
		foreach ($xpath->query('//a[contains(@href, \'profile\')]/strong') as $node)
		{
			$friendName = Strings::removeSpaces($node->nodeValue);

			$stmt = $pdo->prepare('INSERT INTO user_friends(user_id, friend_name) VALUES(?, ?)');
			$stmt->execute([$context->userId, $friendName]);
		}
	}
}
