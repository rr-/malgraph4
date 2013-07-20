<?php
class Model_MixedUserMedia
{
	public function __construct(array $columns)
	{
		foreach ($columns as $key => $value)
		{
			$this->$key = $value;
		}

		if ($this->media == Media::Manga)
		{
			$this->duration = 10;
		}

		$this->completed_duration = $this->duration;
		$this->completed_duration *= $this->media == Media::Anime
			? $this->episodes
			: $this->chapters;

		if (empty($this->title))
		{
			$this->title = 'Unknown ' . Media::toString($this->media) . ' entry #' . $this->mal_id;
		}

		$this->mal_link = 'http://myanimelist.net/' . Media::toString($this->media) . '/' . $this->mal_id;
	}

	public static function getRatingDistribution($media)
	{
		$dist = [];
		$result = R::getAll('SELECT COUNT(*) as count FROM usermedia WHERE media = ? GROUP BY score', [$media]);
		foreach ($result as $score => $columns)
		{
			$count = $columns['count'];
			$dist[$score] = $count;
		}
		return RatingDistribution::fromArray($dist);
	}
}
