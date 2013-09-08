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
		$logger = new Logger($fileName);
		$logger->log('Working');

		register_shutdown_function([get_class(), 'shutdown'], $logger);

		try
		{
			SingleInstance::run($fileName);
			$callback($logger);
		}
		catch (InstanceAlreadyRunningException $e)
		{
			$logger->log('Instance already running');
			$logger->log('Finished with errors');
			self::$finished = true;
			exit(1);
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
