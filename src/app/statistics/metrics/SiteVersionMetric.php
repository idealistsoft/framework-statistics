<?php

namespace app\statistics\metrics;

class SiteVersionMetric extends AbstractStat
{
	function name()
	{
		return 'Site Version';
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
		// load composer.json
		$composer = json_decode( file_get_contents( INFUSE_BASE_DIR . '/composer.json' ) );
		
		// site version
		return $composer->version;
	}
}