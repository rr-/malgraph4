<?php
class UserControllerEntriesModule extends AbstractUserControllerModule
{
	public static function getUrlParts()
	{
		return ['entries'];
	}

	public static function getMediaAvailability()
	{
		return [];
	}

	public static function work(&$viewContext)
	{
		$sender = $_GET['sender'];
		$filterParam = $_GET['filter-param'];

		$viewContext->viewName = 'user-entries-' . $sender;
		$viewContext->layoutName = 'layout-ajax';
		$viewContext->filterParam = $filterParam;

		$computeMeanScore = null;
		switch ($sender)
		{
			case 'ratings':
				$cb = function($row) use ($filterParam) {
					return intval($row->score) == intval($filterParam)
					and $row->status != UserListStatus::Planned; };
				break;
			case 'length':
				$cb = function($row) use ($filterParam) {
					return MediaLengthDistribution::getGroup($row) == $filterParam
					and $row->status != UserListStatus::Planned
					and !($row->sub_type == AnimeMediaType::Movie and $row->media == Media::Anime); };
				$computeMeanScore = true;
				break;
			default:
				throw new Exception('Unknown sender');
		}

		$list = Retriever::getUserMediaList($viewContext->userId, $viewContext->media);
		$list = array_filter($list, $cb);
		$isPrivate = Retriever::isUserMediaListPrivate($viewContext->userId, $viewContext->media);

		if ($computeMeanScore)
		{
			$viewContext->meanScore = Retriever::getMeanScore($list);
		}
		$viewContext->entries = $list;
		$viewContext->isPrivate = $isPrivate;
	}
}
