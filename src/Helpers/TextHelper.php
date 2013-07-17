<?php
class TextHelper
{
	public static function getMediaTitle($title, $media, $id)
	{
		if (!empty($title))
		{
			return $title;
		}
		return 'Unknown ' . Media::toString($media) . ' entry #' . $id;
	}
}
