<?php
class Media extends Enum
{
	const Anime = 'A';
	const Manga = 'M';

	public static function toString($media)
	{
		return Strings::makeEnum($media, [
			self::Anime => 'anime',
			self::Manga => 'manga',
		], null);
	}
}
