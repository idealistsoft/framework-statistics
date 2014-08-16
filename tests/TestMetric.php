<?php

use \app\statistics\metrics\AbstractStat;

class TestMetric extends AbstractStat
{
	function name()
	{
		return 'Test Statistic';
	}

	function granularity()
	{
		return STATISTIC_GRANULARITY_DAY;
	}

	function type()
	{
		return STATISTIC_TYPE_VOLUME;
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
			return 1023;

		return 1000;
	}
}