<?php
abstract class AbstractSubProcessor
{
	static $domCache = [];

	protected static function getDOM(Document $document)
	{
		if (isset(self::$domCache[$document->url]))
		{
			return self::$domCache[$document->url];
		}

		$doc = new DOMDocument;
		$doc->preserveWhiteSpace = false;
		ErrorHandler::suppress();
		$doc->loadHTML($document->content);
		ErrorHandler::restore();

		if (count(self::$domCache) > 20)
		{
			self::$domCache = [];
		}
		self::$domCache[$document->url] = $doc;
		return $doc;
	}

	protected static function getNodeValue(DOMXPath $xpath, $query, DOMNode $parentNode = null, $attrib = null)
	{
		$node = $xpath->query($query, $parentNode)->item(0);
		if (!empty($node))
		{
			return $attrib
				? $node->getAttribute($attrib)
				: $node->nodeValue;
		}
		return null;
	}

	public function insert($tableName, $allRows)
	{
		if (empty($allRows))
		{
			return;
		}
		if (!is_array(reset($allRows)))
		{
			$allRows = [$allRows];
		}

		foreach (array_chunk($allRows, 50) as $rows)
		{
			$columns = array_keys(reset($rows));
			$single = '(' . join(', ', array_fill(0, count($columns), '?')) . ')';
			$sql = sprintf('INSERT INTO %s(%s) VALUES %s',
				$tableName,
				join(', ', $columns),
				join(', ', array_fill(0, count($rows), $single))
			);
			$flattened = call_user_func_array('array_merge', array_map('array_values', $rows));

			$pdo = Database::getPDO();
			$stmt = $pdo->prepare($sql);
			$stmt->execute($flattened);
		}
		return $pdo->lastInsertId();
	}

	public function update($tableName, $conditions, $newData)
	{
		$single1 = [];
		foreach ($newData as $key => $val)
		{
			$single1 []= $key . ' = ?';
		}
		$single2 = [];
		foreach ($conditions as $key => $val)
		{
			$single2 []= $key . ' = ?';
		}
		$sql = sprintf('UPDATE %s SET %s WHERE %s',
			$tableName,
			join(', ', $single1),
			join(' AND ', $single2)
		);
		$flattened = array_merge(array_values($conditions), array_values($newData));

		$pdo = Database::getPDO();
		$stmt = $pdo->prepare($sql);
		$stmt->execute($flattened);
	}

	public function delete($tableName, $conditions)
	{
		$single = [];
		foreach ($conditions as $key => $value)
		{
			$single []= $key . ' = ?';
		}
		$sql = sprintf('DELETE FROM %s WHERE %s',
			$tableName,
			join(' AND ', $single));

		$pdo = Database::getPDO();
		$stmt = $pdo->prepare($sql);
		$stmt->execute(array_values($conditions));
	}

	public abstract function process(array $documents, &$context);
}
