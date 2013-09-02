<?php
class TextHelper
{
	public static function loadJson($path, $fetchAsArray = false)
	{
		$contents = file_get_contents($path);
		$contents = preg_replace('/#(.*)$/m', '', $contents);
		return json_decode($contents, $fetchAsArray);
	}

	public static function loadSimpleList($path)
	{
		$contents = file_get_contents($path);
		$contents = preg_replace('/#(.*)$/m', '', $contents);
		$lines = explode("\n", $contents);
		$lines = array_map('trim', $lines);
		$lines = array_filter($lines);
		return $lines;
	}

	public static function putSimpleList($path, $lines)
	{
		$lines = array_map('trim', $lines);
		$lines = array_filter($lines);
		$contents = implode("\n", $lines);
		file_put_contents($path, $contents);
	}

	public static function putJson($path, $json)
	{
		$contents = json_encode($json);
		file_put_contents($path, $contents);
	}

	public static function getVolumesText($plural, $short = false)
	{
		return self::getMediaCustomUnitText('vol', 'volume', $plural, $short);
	}

	public static function getMediaUnitText($media, $plural = false, $short = false)
	{
		switch ($media)
		{
			case Media::Anime:
				return self::getMediaCustomUnitText('ep', 'episode', $plural, $short);
			case Media::Manga:
				return self::getMediaCustomUnitText('chap', 'chapter', $plural, $short);
		}
		throw new BadMediaException();
	}

	public static function getNumberedMediaUnitText($media, $number, $short = false)
	{
		$plural = false;
		$prefix = $number;
		if ($prefix == 0)
		{
			$prefix = '?';
			$plural = true;
		}
		elseif ($prefix > 1)
		{
			$plural = true;
		}
		$suffix = self::getMediaUnitText($media, $plural, $short);
		return $prefix . ' ' . $suffix;
	}

	public static function getMediaCustomUnitText($shortForm, $longForm, $plural, $short)
	{
		$text = $short ? $shortForm : $longForm;
		if ($plural)
		{
			$text .= 's';
		}
		return $text;
	}

	public static function replaceTokens($input, array $tokens)
	{
		$output = $input;
		foreach ($tokens as $key => $val)
		{
			if (is_object($val) or is_array($val))
			{
				continue;
			}
			$output = str_replace('{' . $key . '}', $val, $output);
		}
		return $output;
	}

	public static function roundPercentages($distribution)
	{
		//largest remainder method
		$total = max(array_sum($distribution), 1);
		$percentages = array_map(function($x) use ($total)
		{
			return $x * 100.0 / $total;
		}, $distribution);

		asort($percentages, SORT_NUMERIC);
		$percentagesRounded = array_map('floor', $percentages);

		$keys = array_keys($percentages);
		$sum = array_sum($percentagesRounded);
		if ($sum == 0)
		{
			return $distribution;
		}
		while ($sum < 100)
		{
			assert(!empty($keys));
			$key = array_shift($keys);
			$percentagesRounded[$key] ++;
			$sum ++;
		}

		return $percentagesRounded;
	}

}
