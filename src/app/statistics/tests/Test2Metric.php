<?php

namespace app\statistics\tests;

class Test2Metric extends \app\statistics\metrics\AbstractStat
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