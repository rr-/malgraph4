<?php
require_once 'src/core.php';

$limit = 500;
$userProcessor = new UserProcessor();
$processed = 0;
$names = [];
while ($processed < $limit)
{
	++ $processed;
	if (empty($names))
	{
		$query = 'SELECT uf.name FROM userfriend uf LEFT JOIN user u ON u.name = uf.name WHERE u.id IS NULL GROUP BY uf.name ORDER BY RANDOM()';
		$rows = R::getAll($query);
		$names = array_map(function($x) { return $x['name']; }, $rows);
		if (empty($names))
		{
			echo 'No more users!';
			exit(1);
		}
	}
	$name = array_shift($names);
	printf('#%03d %s' . PHP_EOL, $processed, $name);
	try
	{
		$userProcessor->process($name);
	}
	catch (Exception $e)
	{
	}
}
exit(0);
