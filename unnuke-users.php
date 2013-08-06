<?php
require_once 'src/core.php';

$limit = 500;
$userProcessor = new UserProcessor();
$done = 0;
$names = [];

$exitCode = 0;
while ($done < $limit)
{
	if (empty($names))
	{
		$query = 'SELECT uf.name FROM userfriend uf' .
			' LEFT JOIN user u ON u.name = uf.name' .
			' WHERE u.id IS NULL' .
			' GROUP BY uf.name' .
			' ORDER BY RANDOM()';

		$rows = R::getAll($query);
		$names = array_map(function($x) { return $x['name']; }, $rows);
		if (empty($names))
		{
			echo 'No more users!';
			exit(0);
		}
	}

	$name = reset($names);
	printf('#%03d %s' . PHP_EOL, $done, $name);
	R::begin();
	try
	{
		$userProcessor->process($name);
		R::commit();
		++ $done;
		array_shift($names);
	}
	catch (Exception $e)
	{
		R::rollback();
		echo $e->getMessage() . PHP_EOL;
		$exitCode = 1;
		array_shift($names);
	}
}
exit($exitCode);
