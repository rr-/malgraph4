<?php
class DistributionEvaluator
{
	public static function evaluate(AbstractDistribution $dist)
	{
		$values = [];
		$allEntries = $dist->getAllEntries();
		$tmpDist = RatingDistribution::fromEntries($allEntries);
		$meanScore = $tmpDist->getMeanScore();
		foreach ($dist->getGroupsKeys() as $safeKey => $key)
		{
			$entry = [];
			$ratingDist = RatingDistribution::fromEntries($dist->getGroupEntries($key));
			$localMeanScore = $ratingDist->getRatedCount() * $ratingDist->getMeanScore() + $ratingDist->getUnratedCount() * $meanScore;
			$localMeanScore /= (float)max(1, $dist->getGroupSize($key));
			$weight = $dist->getGroupSize($key) / max(1, $dist->getLargestGroupSize());
			$weight = 1 - pow(1 - pow($weight, 8. / 9.), 2);
			$value = $meanScore + ($localMeanScore - $meanScore) * $weight;
			$values[$safeKey] = $value;
		}
		return $values;
	}
}
