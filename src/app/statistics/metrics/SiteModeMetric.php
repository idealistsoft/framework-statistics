<?php

namespace app\statistics\metrics;

class SiteModeMetric extends AbstractStat
{
	function name()
	{
		return 'Production Mode';
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
		return 3;
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
		return ($this->app[ 'config' ]->get('site.production-level')) ? '<span style="color:green;">Production</span>' : '<span style="color: #999;">Development</span>';
	}
}