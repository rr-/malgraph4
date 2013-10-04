<?php
require_once __DIR__ . '/../src/core.php';

$processors = [
	'user' => new UserProcessor(),
	'manga' => new MangaProcessor(),
	'anime' => new AnimeProcessor(),
];

array_shift($argv);
$pkey = array_shift($argv);

if (!isset($processors[$pkey]))
{
	printf('Usage: %s %s KEY1 [KEY2, ...]' . PHP_EOL,
		__FILE__, join('|', array_keys($processors)));

	exit(1);
}
$processor = $processors[$pkey];

$exitCode = 0;
foreach ($argv as $key)
{
	printf('Processing %s %s' . PHP_EOL,
		$pkey, is_numeric($key) ? '#' . $key : $key);

	try
	{
		if ($pkey === 'user')
		{
			Database::selectUser($key);
		}
		$processor->process($key);
	}
	catch (BadProcessorKeyException $e)
	{
		echo $e->getMessage() . PHP_EOL;
	}
	catch (DownloadFailureException $e)
	{
		echo $e->getMessage() . PHP_EOL;
		$exitCode = 1;
	}
	catch (Exception $e)
	{
		echo $e . PHP_EOL;
		$exitCode = 1;
	}
}
exit($exitCode);
