<?php
class UserControllerQueuePositionModule extends AbstractUserControllerModule
{
	public static function getUrlParts()
	{
		return ['queue-pos'];
	}

	public static function getMediaAvailability()
	{
		return [];
	}

	public static function preWork(&$controllerContext, &$viewContext)
	{
		parent::preWork($controllerContext, $viewContext);
		$controllerContext->cache->bypass(true);
	}

	public static function work(&$controllerContext, &$viewContext)
	{
		$queue = new Queue(Config::$userQueuePath);
		$j['user'] = $viewContext->user->name;
		$j['pos'] = $viewContext->queuePosition;
		$viewContext->layoutName = 'layout-json';
		$viewContext->json = $j;
	}
}
