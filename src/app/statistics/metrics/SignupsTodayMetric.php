<?php

namespace app\statistics\metrics;

use infuse\Database;

class SignupsTodayMetric extends AbstractStat
{
	function name()
	{
		return 'Signups Today';
	}

	function granularity()
	{
		return STATISTIC_GRANULARITY_DAY;
	}

	function type()
	{
		return STATISTIC_TYPE_FLOW;
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
					'created_at >= ' . $start,
					'created_at <= ' . $end ],
				'single' => true ] );
	}
}