<?php
class Database extends Singleton
{
	private static $pdo;

	public static function doInit()
	{
		self::$pdo = new PDO('sqlite:' . Config::$dbPath);
		self::$pdo->setAttribute(PDO::ATTR_PERSISTENT, true);
		self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		$stmt = self::$pdo->prepare('PRAGMA foreign_keys = ON');
		$stmt->execute();
	}

	public static function getPDO()
	{
		return self::$pdo;
	}

	public static function nuke()
	{
		unlink(Config::$dbPath);
		self::doInit();
	}
}

Database::init();
