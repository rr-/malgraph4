<?php
require_once 'src/core.php';

try
{
	R::freeze(false);
	R::nuke();
	R::exec('CREATE TABLE IF NOT EXISTS user (
		id INTEGER PRIMARY KEY,
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

	R::exec('CREATE TABLE IF NOT EXISTS userfriend (
		id INTEGER PRIMARY KEY,
		user_id INTEGER,
		name VARCHAR(32),
		UNIQUE (user_id, name),
		FOREIGN KEY(user_id) REFERENCES user(id) ON DELETE CASCADE
	)');

	R::exec('CREATE TABLE IF NOT EXISTS userclub (
		id INTEGER PRIMARY KEY,
		user_id INTEGER,
		mal_id INTEGER,
		name VARCHAR(96),
		UNIQUE (user_id, mal_id),
		FOREIGN KEY(user_id) REFERENCES user(id) ON DELETE CASCADE
	)');

	R::exec('CREATE TABLE IF NOT EXISTS userhistory (
		id INTEGER PRIMARY KEY,
		user_id INTEGER,
		mal_id INTEGER,
		media VARCHAR(1),
		progress INTEGER,
		timestamp TIMESTAMP,
		FOREIGN KEY(user_id) REFERENCES user(id) ON DELETE CASCADE
	)');

	R::exec('CREATE TABLE IF NOT EXISTS usermedia (
		id INTEGER PRIMARY KEY,
		user_id INTEGER,
		mal_id INTEGER,
		media VARCHAR(1),
		score INTEGER,
		start_date VARCHAR(10), --TIMESTAMP
		end_date VARCHAR(10), --TIMESTAMP
		status VARCHAR(1),

		finished_episodes INTEGER,
		finished_chapters INTEGER,
		finished_volumes INTEGER,

		UNIQUE (user_id, mal_id, media),
		FOREIGN KEY(user_id) REFERENCES user(id) ON DELETE CASCADE
	)');

	R::exec('CREATE TABLE IF NOT EXISTS media (
		id INTEGER PRIMARY KEY,
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
		publishing_status VARCHAR(1),
		published_from VARCHAR(10), --TIMESTAMP
		published_to VARCHAR(10), --TIMESTAMP
		processed TIMESTAMP,
		franchise VARCHAR(10),

		duration INTEGER,
		episodes INTEGER,
		chapters INTEGER,
		volumes INTEGER,
		serialization_id INTEGER,
		serialization_name VARCHAR(32),

		UNIQUE (mal_id, media)
	)');

	R::exec('CREATE TABLE IF NOT EXISTS mediagenre (
		id INTEGER PRIMARY KEY,
		media_id INTEGER,
		mal_id INTEGER,
		name VARCHAR(30),
		FOREIGN KEY(media_id) REFERENCES media(id) ON DELETE CASCADE
	)');

	R::exec('CREATE TABLE IF NOT EXISTS mediatag (
		id INTEGER PRIMARY KEY,
		media_id INTEGER,
		name INTEGER,
		count VARCHAR(30),
		FOREIGN KEY(media_id) REFERENCES media(id) ON DELETE CASCADE
	)');

	R::exec('CREATE TABLE IF NOT EXISTS mediarelation (
		id INTEGER PRIMARY KEY,
		media_id INTEGER,
		mal_id INTEGER,
		media VARCHAR(1),
		type INTEGER,
		FOREIGN KEY(media_id) REFERENCES media(id) ON DELETE CASCADE
	)');

	R::exec('CREATE TABLE IF NOT EXISTS animeproducer (
		id INTEGER PRIMARY KEY,
		media_id INTEGER,
		mal_id INTEGER,
		name VARCHAR(32),
		FOREIGN KEY(media_id) REFERENCES media(id) ON DELETE CASCADE
	)');

	R::exec('CREATE TABLE IF NOT EXISTS mangaauthor (
		id INTEGER PRIMARY KEY,
		media_id INTEGER,
		mal_id INTEGER,
		name VARCHAR(32),
		FOREIGN KEY(media_id) REFERENCES media(id) ON DELETE CASCADE
	)');
}
catch (Exception $e)
{
	echo $e . PHP_EOL;
}
