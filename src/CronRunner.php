<?php
class CronRunner
{
	private static $finished = false;

	public static function shutdown($logger)
	{
		if (!self::$finished)
		{
			$logger->log('Finished abnormally');
			exit(1);
		}
	}

	public static function run($fileName, $callback)
	{
		try
		{
			SingleInstance::run($fileName);
		}
		catch (InstanceAlreadyRunningException $e)
		{
			self::$finished = true;
			exit(1);
		}

		$logger = new Logger($fileName);
		$logger->log('Working');

		register_shutdown_function([get_class(), 'shutdown'], $logger);

		try
		{
			$callback($logger);
		}
		catch (Exception $e)
		{
			$logger->log($e);
			$logger->log('Finished with errors');
			self::$finished = true;
			exit(1);
		}

		$logger->log('Finished');
		self::$finished = true;
		exit(0);
	}
}
