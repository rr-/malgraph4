<?php
require_once __DIR__ . '/../src/core.php';

$mediaQueue = new Queue(Config::$mediaQueuePath);

$query = 'SELECT media, mal_id FROM media';
$media = R::getAll($query);

$mediaIds = [];
foreach ($media as $entry)
	$mediaIds []= TextHelper::serializeMediaId($entry);

$mediaQueue->enqueueMultiple(array_map(function($mediaId)
	{
		return new QueueItem($mediaId);
	}, $mediaIds));
