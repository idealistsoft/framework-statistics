<?php

namespace app\statistics\metrics;

use infuse\Database;

use app\statistics\models\Statistic;

class DatabaseTablesMetric extends AbstractStat
{
	function name()
	{
		return 'Database Tables';
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
		return 2;
	}

	function hasChart()
	{
		return false;
	}

	function hasDelta()
	{
		return false;
	}

	function shouldBeCaptured()
	{
		return false;
	}

	function value( $start, $end )
	{
		// calculate metric as of right now
		$query = Database::sql("SHOW table STATUS");
		
		$status = $query->fetchAll( \PDO::FETCH_ASSOC );
		
		return count( $status );
	}
}