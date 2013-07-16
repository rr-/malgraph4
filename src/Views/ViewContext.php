<?php
class ViewContext
{
	public $name;

	public function __construct()
	{
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
			'/media/js/glider.js',
			'/media/js/misc.js',
		];
	}
}
