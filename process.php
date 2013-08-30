<?php
require_once 'src/core.php';

$processors = [
	'user' => new UserProcessor(),
	'manga' => new MangaProcessor(),
	'anime' => new AnimeProcessor(),
];

array_shift($argv);
$pkey = array_shift($argv);
if (!isset($processors[$pkey]))
{
	echo 'Usage: ' . __FILE__ . ' ' . join('|', array_keys($processors)) . ' KEY1 [KEY2, ...]' . PHP_EOL;
	exit(1);
}
$processor = $processors[$pkey];

$exitCode = 0;
foreach ($argv as $key)
{
	printf('Processing %s %s' . PHP_EOL, $pkey, is_numeric($key) ? '#' . $key : $key);
	try
	{
		$processor->process($key);
	}
	catch (BadProcessorKeyException $e)
	{
		echo $e->getMessage() . PHP_EOL;
	}
	catch (Exception $e)
	{
		echo $e->getMessage() . PHP_EOL;
		$exitCode = 1;
	}
}
exit($exitCode);
