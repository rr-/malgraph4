<?php
require_once __DIR__ . '/../src/core.php';

$mediaQueue = new Queue(Config::$mediaQueuePath);

$mediaIds = [];
foreach (R::findAll('media') as $entry)
	$mediaIds []= TextHelper::serializeMediaId($entry);

$mediaQueue->enqueueMultiple(array_map(function($mediaId)
	{
		return new QueueItem($mediaId);
	}, $mediaIds));
