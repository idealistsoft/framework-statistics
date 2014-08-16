<?php

use \app\statistics\metrics\AbstractStat;

class Test2Metric extends AbstractStat
{
	function name()
	{
		return 'Test Statistic';
	}

	function granularity()
	{
		return STATISTIC_GRANULARITY_MONTH;
	}

	function type()
	{
		return STATISTIC_TYPE_FLOW;
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
		if( $end < time() )
			return 123;

		return 100;
	}
}