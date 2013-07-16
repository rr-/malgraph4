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
		];
		$this->meta->scripts = [
			'http://code.jquery.com/jquery-1.10.2.min.js',
			'/media/js/glider.js',
			'/media/js/misc.js',
		];
	}
}
