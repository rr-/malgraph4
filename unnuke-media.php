<?php
require_once 'src/core.php';

$mediaProcessors =
[
	Media::Anime => new AnimeProcessor(),
	Media::Manga => new MangaProcessor()
];

$query = 'SELECT um.mal_id, um.media FROM usermedia um' .
	' GROUP BY um.media, um.mal_id' .
	' HAVING NOT EXISTS(' .
		'SELECT null FROM media m' .
		' WHERE m.mal_id = um.mal_id AND m.media = um.media' .
	') ORDER BY um.mal_id';
$rows = R::getAll($query);
$rows = ReflectionHelper::arraysToClasses($rows);
$done = 0;

$exitCode = 0;
foreach (array_chunk($rows, Config::$transactionCommitFrequency) as $chunk)
{
	try
	{
		R::transaction(function() use ($mediaProcessors, $chunk, &$done, $rows)
		{
			$length = strlen(count($rows));
			foreach ($chunk as $row)
			{
				++ $done;
				printf("(%0${length}d/%d) Processing %s #%d" . PHP_EOL,
					$done, count($rows),
					Media::toString($row->media), $row->mal_id);

				$mediaProcessors[$row->media]->process($row->mal_id);
			}
		});
	}
	catch (Exception $e)
	{
		echo $e->getMessage() . PHP_EOL;
		$exitCode = 1;
	}
}

exit($exitCode);
