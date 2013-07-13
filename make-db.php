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
		user_mal_id INTEGER,
		comment_count INTEGER,
		post_count INTEGER,
		birthday VARCHAR(10), --TIMESTAMP
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

	$pdo->exec('CREATE TABLE IF NOT EXISTS user_media (
		user_media_id INTEGER PRIMARY KEY,
		user_id INTEGER,
		media_mal_id INTEGER,
		media VARCHAR(1),
		score INTEGER,
		start_date VARCHAR(10), --TIMESTAMP
		end_date VARCHAR(10), --TIMESTAMP
		status VARCHAR(1),
		UNIQUE (media_mal_id, media),
		FOREIGN KEY(user_id) REFERENCES users(user_id)
		ON DELETE CASCADE
	)');

	$pdo->exec('CREATE TABLE IF NOT EXISTS user_media_anime_data (
		user_media_id INTEGER,
		episodes INTEGER,
		FOREIGN KEY(user_media_id) REFERENCES user_media(user_media_id)
		ON DELETE CASCADE
	)');

	$pdo->exec('CREATE TABLE IF NOT EXISTS user_media_manga_data (
		user_media_id INTEGER,
		chapters INTEGER,
		volumes INTEGER,
		FOREIGN KEY(user_media_id) REFERENCES user_media(user_media_id)
		ON DELETE CASCADE
	)');

	$pdo->exec('CREATE TABLE IF NOT EXISTS media (
		media_id INTEGER PRIMARY KEY,
		media_mal_id INTEGER,
		media VARCHAR(1),
		title VARCHAR(96),
		sub_type INTEGER,
		picture_url VARCHAR(256),
		ranking INTEGER,
		status VARCHAR(1),
		published_from VARCHAR(10), --TIMESTAMP
		published_to VARCHAR(10), --TIMESTAMP
		UNIQUE (media_mal_id, media)
	)');

	$pdo->exec('CREATE TABLE IF NOT EXISTS media_anime_data (
		media_id INTEGER,
		duration INTEGER,
		episode_count INTEGER,
		FOREIGN KEY(media_id) REFERENCES media(media_id)
		ON DELETE CASCADE
	)');

	$pdo->exec('CREATE TABLE IF NOT EXISTS media_manga_data (
		media_id INTEGER,
		chapter_count INTEGER,
		volume_count INTEGER,
		FOREIGN KEY(media_id) REFERENCES media(media_id)
		ON DELETE CASCADE
	)');

	$pdo->exec('CREATE TABLE IF NOT EXISTS media_genres (
		media_id INTEGER,
		genre_mal_id INTEGER,
		genre_name VARCHAR(30),
		FOREIGN KEY(media_id) REFERENCES media(media_id)
		ON DELETE CASCADE
	)');

	$pdo->exec('CREATE TABLE IF NOT EXISTS media_tags (
		media_id INTEGER,
		tag_name INTEGER,
		tag_count VARCHAR(30),
		FOREIGN KEY(media_id) REFERENCES media(media_id)
		ON DELETE CASCADE
	)');

	$pdo->exec('CREATE TABLE IF NOT EXISTS media_relations (
		media_id INTEGER,
		media_mal_id INTEGER,
		media VARCHAR(1),
		relation_type INTEGER,
		FOREIGN KEY(media_id) REFERENCES media(media_id)
		ON DELETE CASCADE
	)');
}
catch (Exception $e)
{
	echo $e . PHP_EOL;
}
