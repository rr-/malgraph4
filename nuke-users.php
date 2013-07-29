<?php
require_once 'src/core.php';
$tables = ['userfriend', 'userclub', 'usermedia', 'userhistory', 'user'];
foreach ($tables as $table)
{
	echo 'Deleting from ' . $table . PHP_EOL;
	R::exec('DELETE FROM ' . $table);
}
