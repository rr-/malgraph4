<?php
class MediaSubProcessorFranchises extends MediaSubProcessor
{
	private static function mediaToKey($mediaRow)
	{
		$mediaArray = (array) $mediaRow;
		return $mediaRow['media'] . $mediaRow['mal_id'];
	}

	public function process(array $documents, &$context)
	{
		$franchiseIds = [];
		$franchiseIds []= self::mediaToKey($context->media);
		foreach ($context->relationData as $relation)
		{
			if ($relation['media'] != $context->media->media)
			{
				continue;
			}
			if (in_array($relation['type'], [MediaRelation::Other, MediaRelation::Character/*, MediaRelation::SpinOff, MediaRelation::AlternativeSetting*/]))
			{
				continue;
			}
			$franchiseIds []= self::mediaToKey($relation);
		}
		foreach (R::findAll('media', 'media||mal_id IN (' . R::genSlots($franchiseIds) . ')', $franchiseIds) as $relatedMedia)
		{
			$franchiseIds []= $relatedMedia->franchise;
		}

		$franchiseId = reset($franchiseIds);

		$media = &$context->media;
		$media->franchise = $franchiseId;
		R::store($media);

		$sql = 'UPDATE media SET franchise = ? WHERE franchise IN (' . R::genSlots($franchiseIds) . ')';
		R::exec($sql, array_merge([$franchiseId], $franchiseIds));
	}
}
