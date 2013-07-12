<?php
require_once 'src/core.php';

try
{
	Database::nuke();
	$pdo = Database::getPDO();
	$pdo->exec('CREATE TABLE IF NOT EXISTS users (
		user_id INTEGER PRIMARY KEY,
		name VARCHAR(32) UNIQUE,
		picture VARCHAR(256),
		join_date TIMESTAMP,
		mal_id INTEGER,
		comment_count INTEGER,
		post_count INTEGER,
		birthday TIMESTAMP,
		location VARCHAR(100),
		website VARCHAR(100),
		gender VARCHAR(1)
	)');

	$pdo->exec('CREATE TABLE IF NOT EXISTS user_anime_data (
		user_id INTEGER,
		view_count INTEGER,
		days_spent FLOAT,
		FOREIGN KEY(user_id) REFERENCES users(user_id)
		ON DELETE CASCADE
	)');

	$pdo->exec('CREATE TABLE IF NOT EXISTS user_manga_data (
		user_id INTEGER,
		view_count INTEGER,
		days_spent FLOAT,
		FOREIGN KEY(user_id) REFERENCES users(user_id)
		ON DELETE CASCADE
	)');

	$pdo->exec('CREATE TABLE IF NOT EXISTS user_friends (
		user_id INTEGER,
		friend_name VARCHAR(32) UNIQUE,
		FOREIGN KEY(user_id) REFERENCES users(user_id)
		ON DELETE CASCADE
	)');

	$pdo->exec('CREATE TABLE IF NOT EXISTS user_clubs (
		user_id INTEGER,
		club_id INTEGER UNIQUE,
		club_name VARCHAR(96),
		FOREIGN KEY(user_id) REFERENCES users(user_id)
		ON DELETE CASCADE
	)');

	$pdo->exec('CREATE TABLE IF NOT EXISTS user_history (
		user_history_id INTEGER PRIMARY KEY,
		user_id INTEGER,
		media_id INTEGER,
		media VARCHAR(1),
		progress INTEGER,
		timestamp TIMESTAMP,
		FOREIGN KEY(user_id) REFERENCES users(user_id)
		ON DELETE CASCADE
	)');
}
catch (Exception $e)
{
	echo $e . PHP_EOL;
}