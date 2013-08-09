<?php
class Database extends Singleton
{
	public static function doInit()
	{
		include implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'lib', 'redbean', 'RedBean', 'redbean.inc.php']);

		R::setup('sqlite:' . Config::$dbPath);
		R::freeze(true);
		R::exec('PRAGMA foreign_keys=ON');
		R::exec('PRAGMA temp_store=MEMORY');

		ReflectionHelper::loadClasses(__DIR__ . DIRECTORY_SEPARATOR . 'Models');
	}
}

Database::init();
