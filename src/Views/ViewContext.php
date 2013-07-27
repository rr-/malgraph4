<?php
class ViewContext
{
	public $name;

	public function __construct()
	{
		$this->layoutName = 'layout';
		$this->viewName = null;
		$this->renderStart = microtime(true);
		$this->meta = new StdClass;
		$this->meta->keywords = ['malgraph', 'anime', 'manga', 'statistics', 'stats'];
		$this->meta->description = 'MALgraph - an extension of your MyAnimeList profile. Check your rating distribution, get anime or manga recommendations, and compare numerous stats with other kawaii Japanese otaku.';
		$this->meta->styles = [];
		$this->meta->scripts = [];
		WebMediaHelper::addBasic($this);
	}
}
