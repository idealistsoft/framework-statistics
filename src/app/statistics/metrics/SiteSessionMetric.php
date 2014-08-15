<?php

namespace app\statistics\metrics;

class SiteSessionMetric extends AbstractStat
{
	function name()
	{
		return 'Session Adapter';
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
		return $this->app[ 'config' ]->get( 'session.adapter' );
	}
}