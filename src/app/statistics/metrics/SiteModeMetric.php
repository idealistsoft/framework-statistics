<?php

namespace app\statistics\metrics;

class SiteModeMetric extends AbstractStat
{
    public function name()
    {
        return 'Production Mode';
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
        return 3;
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
        return ($this->app[ 'config' ]->get('site.production-level')) ? '<span style="color:green;">Production</span>' : '<span style="color: #999;">Development</span>';
    }
}
