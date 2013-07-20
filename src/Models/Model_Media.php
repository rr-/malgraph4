<?php
R::dependencies([
	'mediatag' => ['media'],
	'mediarelation' => ['media'],
	'mediagenre' => ['media'],
	'animeproducer' => ['media'],
	'mangaauthor' => ['media'],
]);

class Model_Media extends RedBean_SimpleModel
{
	public static function getCount($media)
	{
		return R::getAll('SELECT COUNT(*) AS count FROM media WHERE media = ?', [$media])[0]['count'];
	}
}
