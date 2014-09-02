<?php

use \app\statistics\metrics\AbstractStat;

class Test2Metric extends AbstractStat
{
    public function name()
    {
        return 'Test Statistic';
    }

    public function granularity()
    {
        return STATISTIC_GRANULARITY_MONTH;
    }

    public function type()
    {
        return STATISTIC_TYPE_FLOW;
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

            return 123;

        return 100;
    }
}
