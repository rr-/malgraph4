<?php
require_once 'src/core.php';
$tables = ['mediagenre', 'mediatag', 'mediarelation', 'animeproducer', 'mangaauthor', 'media'];
foreach ($tables as $table)
{
	echo 'Deleting from ' . $table . PHP_EOL;
	R::exec('DELETE FROM ' . $table);
}
