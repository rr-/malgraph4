<?php
require_once 'src/core.php';

try
{
	Database::nuke();
	$pdo = Database::getPDO();
	$pdo->exec('CREATE TABLE IF NOT EXISTS users (
		user_id INTEGER PRIMARY KEY,
		name VARCHAR(32) UNIQUE,
		picture_url VARCHAR(256),
		join_date VARCHAR(10), --TIMESTAMP
		mal_id INTEGER,
		comments INTEGER,
		posts INTEGER,
		birthday VARCHAR(10), --TIMESTAMP
		location VARCHAR(100),
		website VARCHAR(100),
		gender VARCHAR(1),
		processed TIMESTAMP,

		anime_views INTEGER,
		anime_days_spent FLOAT,
		anime_private BOOLEAN,
		manga_views INTEGER,
		manga_days_spent FLOAT,
		manga_private BOOLEAN
	)');

	$pdo->exec('CREATE TABLE IF NOT EXISTS user_friends (
		user_id INTEGER,
		name VARCHAR(32),
		UNIQUE (user_id, name),
		FOREIGN KEY(user_id) REFERENCES users(user_id) ON DELETE CASCADE
	)');

	$pdo->exec('CREATE TABLE IF NOT EXISTS user_clubs (
		user_id INTEGER,
		mal_id INTEGER,
		name VARCHAR(96),
		UNIQUE (user_id, mal_id),
		FOREIGN KEY(user_id) REFERENCES users(user_id) ON DELETE CASCADE
	)');

	$pdo->exec('CREATE TABLE IF NOT EXISTS user_history (
		user_history_id INTEGER PRIMARY KEY,
		user_id INTEGER,
		mal_id INTEGER,
		media VARCHAR(1),
		progress INTEGER,
		timestamp TIMESTAMP,
		FOREIGN KEY(user_id) REFERENCES users(user_id) ON DELETE CASCADE
	)');

	$pdo->exec('CREATE TABLE IF NOT EXISTS user_media_list (
		user_media_id INTEGER PRIMARY KEY,
		user_id INTEGER,
		mal_id INTEGER,
		media VARCHAR(1),
		score INTEGER,
		start_date VARCHAR(10), --TIMESTAMP
		end_date VARCHAR(10), --TIMESTAMP
		status VARCHAR(1),

		episodes INTEGER,
		chapters INTEGER,
		volumes INTEGER,

		UNIQUE (user_id, mal_id, media),
		FOREIGN KEY(user_id) REFERENCES users(user_id) ON DELETE CASCADE
	)');

	$pdo->exec('CREATE TABLE IF NOT EXISTS media (
		media_id INTEGER PRIMARY KEY,
		mal_id INTEGER,
		media VARCHAR(1),
		title VARCHAR(96),
		sub_type INTEGER,
		picture_url VARCHAR(256),
		average_score FLOAT,
		ranking INTEGER,
		popularity INTEGER,
		members INTEGER,
		favorites INTEGER,
		status VARCHAR(1),
		published_from VARCHAR(10), --TIMESTAMP
		published_to VARCHAR(10), --TIMESTAMP
		processed TIMESTAMP,

		duration INTEGER,
		episodes INTEGER,
		chapters INTEGER,
		volumes INTEGER,
		serialization_id INTEGER,
		serialization_name VARCHAR(32),

		UNIQUE (mal_id, media)
	)');

	$pdo->exec('CREATE TABLE IF NOT EXISTS media_genres (
		media_id INTEGER,
		mal_id INTEGER,
		name VARCHAR(30),
		FOREIGN KEY(media_id) REFERENCES media(media_id) ON DELETE CASCADE
	)');

	$pdo->exec('CREATE TABLE IF NOT EXISTS media_tags (
		media_id INTEGER,
		name INTEGER,
		count VARCHAR(30),
		FOREIGN KEY(media_id) REFERENCES media(media_id) ON DELETE CASCADE
	)');

	$pdo->exec('CREATE TABLE IF NOT EXISTS media_relations (
		media_id INTEGER,
		mal_id INTEGER,
		media VARCHAR(1),
		type INTEGER,
		FOREIGN KEY(media_id) REFERENCES media(media_id) ON DELETE CASCADE
	)');

	$pdo->exec('CREATE TABLE IF NOT EXISTS anime_producers (
		media_id INTEGER,
		mal_id INTEGER,
		name VARCHAR(32),
		FOREIGN KEY(media_id) REFERENCES media(media_id) ON DELETE CASCADE
	)');

	$pdo->exec('CREATE TABLE IF NOT EXISTS manga_authors (
		media_id INTEGER,
		mal_id INTEGER,
		name VARCHAR(32),
		FOREIGN KEY(media_id) REFERENCES media(media_id) ON DELETE CASCADE
	)');
}
catch (Exception $e)
{
	echo $e . PHP_EOL;
}
