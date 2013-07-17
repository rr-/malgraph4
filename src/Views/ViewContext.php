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
		$this->meta->styles = [
			'/media/css/core.css',
			'/media/css/menu.css',
			'/media/css/header.css',
			'/media/css/glider.css',
			'/media/css/icons.css',
			'http://fonts.googleapis.com/css?family=Open+Sans|Ubuntu',
		];
		$this->meta->scripts = [
			'http://code.jquery.com/jquery-1.10.2.min.js',
			'/media/js/misc.js',
			'/media/js/glider.js',
			'http://jscrollpane.kelvinluck.com/script/jquery.jscrollpane.min.js',
		];
		$this->meta->keywords = ['malgraph', 'anime', 'manga', 'statistics', 'stats'];
		$this->meta->description = 'MALgraph - an extension of your MyAnimeList profile. Check your rating distribution, get anime or manga recommendations, and compare numerous stats with other kawaii Japanese otaku.';
	}
}
