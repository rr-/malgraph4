<?php
require_once 'src/core.php';

foreach (Database::getAllDbNames() as $dbName)
{
	echo $dbName . ': ';
	Database::attachDatabase($dbName);
	$tables = ['userfriend', 'userclub', 'usermedia', 'userhistory', 'user'];
	foreach ($tables as $table)
	{
		echo $table . '... ';
		R::exec('DELETE FROM ' . $table);
	}
	echo PHP_EOL;
}
