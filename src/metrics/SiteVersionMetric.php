<?php

namespace app\statistics\metrics;

class SiteVersionMetric extends AbstractStat
{
    public function name()
    {
        return 'Site Version';
    }

    public function granularity()
    {
        return STATISTIC_GRANULARITY_DAY;
    }

    public function type()
    {
        return STATISTIC_TYPE_VOLUME;
    }

    public function span()
    {
        return 2;
    }

    public function hasChart()
    {
        return false;
    }

    public function hasDelta()
    {
        return false;
    }

    public function shouldBeCaptured()
    {
        return false;
    }

    public function value($start, $end)
    {
        // load composer.json
        $composer = json_decode( file_get_contents( INFUSE_BASE_DIR . '/composer.json' ) );

        // site version
        return $composer->version;
    }
}
