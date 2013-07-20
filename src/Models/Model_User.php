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
}
