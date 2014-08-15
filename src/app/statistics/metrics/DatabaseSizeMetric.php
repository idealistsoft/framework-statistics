<?php

namespace app\statistics\metrics;

use infuse\Database;

use app\statistics\models\Statistic;

class DatabaseSizeMetric extends AbstractStat
{
	function name()
	{
		return 'Database Size';
	}

	function granularity()
	{
		return STATISTIC_GRANULARITY_DAY;
	}

	function type()
	{
		return STATISTIC_TYPE_VOLUME;
	}

	function span()
	{
		return 12;
	}

	function hasChart()
	{
		return true;
	}

	function hasDelta()
	{
		return true;
	}

	function shouldBeCaptured()
	{
		return true;
	}

	function suffix()
	{
		return 'B';
	}

	function value( $start, $end )
	{
		// only calculate metric as of right now
		$query = Database::sql("SHOW table STATUS");
		
		$status = $query->fetchAll( \PDO::FETCH_ASSOC );
		
		$dbsize = 0;
		// Calculate DB size by adding table size + index size:
		foreach( $status as $row )
			$dbsize += $row['Data_length'] + $row['Index_length'];

		return $dbsize;
	}

	function computeDelta()
	{
		// get metric from yesterday
		$yesterdaysDate = date( 'Y-m-d', strtotime( 'yesterday' ) );

		$stat = Statistic::findOne( [
			'where' => [
				'metric' => $this->key(),
				'day' => $yesterdaysDate ] ] );

		$previous = ($stat) ? $stat->val : 0;

		return $this->computeValue() - $previous;
	}
}