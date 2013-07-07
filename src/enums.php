<?php
abstract class Enum
{
	public static function getConstList()
	{
		return (new ReflectionClass(get_called_class()))->getConstants();
	}
}

class UserGender extends Enum
{
	const Male = 'M';
	const Female = 'F';
	const Unknown = '?';
}

class Media extends Enum
{
	const Anime = 'A';
	const Manga = 'M';
}

class UserListStatus extends Enum
{
	const Dropped = 'D';
	const OnHold = 'H';
	const Completing = 'C';
	const Finished = 'F';
	const Planned = 'P';
	const Unknown = '?';
}
