<?php
class Model_Media extends RedBean_SimpleModel
{
	public static function getCount($media)
	{
		$query = 'SELECT COUNT(*) AS count FROM media WHERE media = ?';
		return intval(R::getAll($query, [$media])[0]['count']);
	}
}
