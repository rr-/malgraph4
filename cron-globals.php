<?php
require_once 'src/core.php';

foreach (Media::getConstList() as $media)
{
	Model_MixedUserMedia::getRatingDistribution($media, true);
}
