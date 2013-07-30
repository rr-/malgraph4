<?php
require_once 'src/core.php';

$limit = 500;
$userProcessor = new UserProcessor();
$processed = 0;
$names = [];

$exitCode = 0;
$attempts = 0;
while ($processed < $limit)
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
	printf('#%03d %s' . PHP_EOL, $processed, $name);
	try
	{
		$userProcessor->process($name);
		++ $processed;
		array_shift($names);
		$attempts = 0;
	}
	catch (Exception $e)
	{
		echo $e->getMessage() . PHP_EOL;
		$exitCode = 1;
		$attempts ++;
		if ($attempts >= 3)
		{
			array_shift($names);
			$attempts = 0;
		}
		else
		{
			sleep(1);
		}
	}
}
exit($exitCode);
