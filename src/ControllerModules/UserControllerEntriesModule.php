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
		$filterParam = isset($_GET['filter-param']) ? $_GET['filter-param'] : null;
		if (isset($_GET['media']) and in_array($_GET['media'], Media::getConstList()))
		{
			$viewContext->media = $_GET['media'];
		}

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
			case 'franchises':
				$cb = function($row) {
					return $row->status != UserListStatus::Planned; };
				break;
			case 'mismatches':
				$cb = function($row) { return true; };
				break;
			default:
				throw new Exception('Unknown sender (' . $sender . ')');
		}

		$list = $viewContext->user->getMixedUserMedia($viewContext->media);
		$list = array_filter($list, $cb);
		$isPrivate = $viewContext->user->isUserMediaPrivate($viewContext->media);

		if (!$isPrivate)
		{
			if ($computeMeanScore)
			{
				$viewContext->meanScore = Retriever::getMeanScore($list);
			}
			if ($sender == 'franchises')
			{
				$franchises = $viewContext->user->getFranchisesFromUserMedia($list);
				$viewContext->franchises = array_filter($franchises, function($franchise) { return count($franchise->ownEntries) > 1; });
			}
			elseif ($sender == 'mismatches')
			{
				$viewContext->entries = $viewContext->user->getMismatchedUserMedia($list);
			}
			else
			{
				$viewContext->entries = $list;
			}
		}

		$viewContext->isPrivate = $isPrivate;
	}
}
