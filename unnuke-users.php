<?php
require_once 'src/core.php';

$limit = 500;
$userProcessor = new UserProcessor();

$query = 'SELECT uf.name FROM userfriend uf' .
	' LEFT JOIN user u ON u.name = uf.name' .
	' WHERE u.id IS NULL' .
	' GROUP BY uf.name' .
	' ORDER BY RANDOM()' .
	' LIMIT ?';
$rows = R::getAll($query, [$limit]);
$rows = ReflectionHelper::arraysToClasses($rows);
$done = 0;

$exitCode = 0;
foreach ($rows as $row)
{
	try
	{
		R::transaction(function() use ($userProcessor, $row, &$done, $rows)
		{
			$length = strlen(count($rows));
			++ $done;
			printf("(%0${length}d/%d) Processing user %s" . PHP_EOL,
				$done, count($rows), $row->name);

			$userProcessor->process($row->name);
		});
	}
	catch (Exception $e)
	{
		echo $e->getMessage() . PHP_EOL;
		$exitCode = 1;
	}
}
exit($exitCode);
