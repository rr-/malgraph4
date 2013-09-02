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

	public static function insert($tableName, $allRows)
	{
		if (empty($allRows))
		{
			return;
		}
		if (!is_array(reset($allRows)))
		{
			$allRows = [$allRows];
		}

		$lastInsertId = null;
		foreach (array_chunk($allRows, Config::$maxDbBindings) as $rows)
		{
			$columns = array_keys(reset($rows));
			$single = '(' . join(', ', array_fill(0, count($columns), '?')) . ')';
			$query = sprintf('INSERT INTO %s(%s) VALUES %s',
				$tableName,
				join(', ', $columns),
				join(', ', array_fill(0, count($rows), $single))
			);
			$flattened = call_user_func_array('array_merge', array_map('array_values', $rows));

			$lastInsertId = R::exec($query, $flattened);
		}
		return $lastInsertId;
	}

	public static function delete($tableName, $conditions)
	{
		$single = [];
		foreach ($conditions as $key => $value)
		{
			$single []= $key . ' = ?';
		}
		$query = sprintf('DELETE FROM %s WHERE %s',
			$tableName,
			join(' AND ', $single));

		R::exec($query, array_values($conditions));
	}
}

Database::init();
