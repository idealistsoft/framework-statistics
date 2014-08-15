<?php

namespace app\statistics\metrics;

use infuse\Database;

class UsersMetric extends AbstractStat
{
	function name()
	{
		return 'Total Users';
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
		return 4;
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

	function value( $start, $end )
	{
		return (int)Database::select(
			'Users',
			'count(uid)',
			[
				'where' => [
					'created_at <= ' . $end ],
				'single' => true ] );
	}
}