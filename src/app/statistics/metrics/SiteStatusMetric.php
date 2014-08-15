<?php

namespace app\statistics\metrics;

class SiteStatusMetric extends AbstractStat
{
	function name()
	{
		return 'Site Status';
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
		return ($this->app[ 'config' ]->get('site.disabled')) ? '<span style="color:red;">Disabled</span>' : '<span style="color:green;">Enabled</span>';
	}
}