<?php
R::dependencies([
	'userfriend' => ['user'],
	'userclub' => ['user'],
	'usermedia' => ['user'],
	'userhistory' => ['user'],
]);

class Model_User extends RedBean_SimpleModel
{
	public function getMixedUserMedia($media)
	{
		$query = 'SELECT m.*, um.* FROM usermedia um LEFT JOIN media m ON m.media = um.media AND m.mal_id = um.mal_id WHERE um.user_id = ? AND um.media = ?';
		$rows = R::getAll($query, [$this->id, $media]);
		$result = array_map(function($row) { return new Model_MixedUserMedia($row); }, $rows);
		return $result;
	}

	public function isUserMediaPrivate($media)
	{
		return $this->{Media::toString($media) . '_private'};
	}

	public static function getCount()
	{
		return R::getAll('SELECT COUNT(*) AS count FROM user')[0]['count'];
	}

	public function getMismatchedUserMedia(array $entries)
	{
		$entriesMismatched = [];
		foreach ($entries as $entry)
		{
			if ($entry->media == Media::Anime)
			{
				$a = $entry->finished_episodes;
				$b = $entry->episodes;
			} else {
				$a = $entry->finished_chapters;
				$b = $entry->chapters;
			}
			if ($a != $b and ($b > 0 or $entry->publishing_status == MediaStatus::Publishing) and $entry->status == UserListStatus::Finished)
			{
				$entriesMismatched []= $entry;
			}
		}
		return $entriesMismatched;
	}

	public function getFranchisesFromUserMedia(array $entries)
	{
		$clusters = [];
		foreach ($entries as $entry)
		{
			if (!isset($clusters[$entry->franchise]))
			{
				$clusters[$entry->franchise] = [];
			}
			$clusters[$entry->franchise] []= $entry;
		}

		$franchises = [];
		foreach ($clusters as $cluster)
		{
			$franchise = new StdClass;
			#$franchise->allEntries = [];
			$franchise->ownEntries = array_values($cluster);
			$franchises []= $franchise;
		}
		return $franchises;
	}
}
