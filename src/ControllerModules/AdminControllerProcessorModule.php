<?php
class AdminControllerProcessorModule extends AbstractControllerModule
{
	public static function getUrlParts()
	{
		return ['a/process'];
	}

	public static function url()
	{
		return '/a/process';
	}

	private static function getChosenMedia($modelIds)
	{
		$chosenMedia =
		[
			Media::Anime => [],
			Media::Manga => [],
		];

		foreach ($modelIds as $modelId)
		{
			if (!preg_match('/([AM])(\d+)(-\1(\d+))?/i', $modelId, $matches))
			{
				throw new Exception('Bad media ID: ' . $modelId);
			}

			$media = strtoupper($matches[1]);
			$mediaId1 = intval($matches[2]);
			$mediaId2 = isset($matches[4]) ? intval($matches[4]) : $mediaId1;
			foreach (range($mediaId1, $mediaId2) as $mediaId)
			{
				$chosenMedia[$media] []= $mediaId;
			}
		}

		foreach ($chosenMedia as $media => $ids)
		{
			$chosenMedia[$media] = array_unique($ids);
		}

		return $chosenMedia;
	}

	private static function getChosenUsers($modelIds)
	{
		$chosenUsers = [];
		foreach ($modelIds as $modelId)
		{
			$chosenUsers []= $modelId;
		}
		return $chosenUsers;
	}

	public static function work(&$viewContext)
	{
		try
		{
			if (empty($_POST['sender']))
			{
				throw new Exception('No sender specified');
			}
			$sender = $_POST['sender'];

			if (empty($_POST['action']))
			{
				throw new Exception('No action specified');
			}
			$action = $_POST['action'];

			if (empty($_POST['model-ids']))
			{
				throw new Exception('No model ids specified');
			}
			$modelIds = array_map('trim', preg_split('/[,;]/', $_POST['model-ids']));

			$chosenMedia = [];
			$chosenUsers = [];
			switch ($sender)
			{
				case 'media':
					$chosenMedia = self::getChosenMedia($modelIds);
					break;
				case 'user':
					$chosenUsers = self::getChosenUsers($modelIds);
					break;
				default:
					throw new Exception('Unknown sender: ' . $sender);
			}

			if ($action == 'refresh')
			{
				$num = 0;
				$startTime = microtime(true);
				$mediaProcessors =
				[
					Media::Anime => new AnimeProcessor(),
					Media::Manga => new MangaProcessor(),
				];
				$userProcessor = new UserProcessor();

				foreach ($chosenMedia as $media => $ids)
				{
					foreach ($ids as $id)
					{
						R::begin();
						$mediaProcessors[$media]->process($id);
						R::rollback();
						++ $num;
					}
				}
				foreach ($chosenUsers as $user)
				{
					R::begin();
					$userProcessor->process($user);
					R::rollback();
					++ $num;
				}

				$viewContext->messageType = 'info';
				$viewContext->message = sprintf('Successfully processed %d entities in %.02fs', $num, microtime(true) - $startTime);
			}
			else
			{
				throw new Exception('Unknown action: ' . $action);
			}
		}
		catch (Exception $e)
		{
			$viewContext->messageType = 'error';
			$viewContext->message = $e->getMessage();
		}

		$viewContext->viewName = 'admin-index';
		$viewContext->meta->title = 'MALgraph - admin';
		WebMediaHelper::addCustom($viewContext);
	}
}
