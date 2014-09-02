<?php

use \app\statistics\metrics\AbstractStat;

class TestMetric extends AbstractStat
{
    public function name()
    {
        return 'Test Statistic';
    }

    public function granularity()
    {
        return STATISTIC_GRANULARITY_DAY;
    }

    public function type()
    {
        return STATISTIC_TYPE_VOLUME;
    }

    public function hasChart()
    {
        return true;
    }

    public function hasDelta()
    {
        return true;
    }

    public function shouldBeCaptured()
    {
        return true;
    }

    public function value($start, $end)
    {
        if( $end < time() )

            return 1023;

        return 1000;
    }
}
