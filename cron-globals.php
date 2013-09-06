<?php
require_once 'src/core.php';
CronRunner::run(__FILE__, function($logger)
{
	foreach (Media::getConstList() as $media)
	{
		Model_MixedUserMedia::getRatingDistribution($media, true);
	}
});
