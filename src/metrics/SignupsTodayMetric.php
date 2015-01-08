<?php

namespace app\statistics\metrics;

use infuse\Utility as U;

class SignupsTodayMetric extends AbstractStat
{
    public function name()
    {
        return 'Signups Today';
    }

    public function granularity()
    {
        return STATISTIC_GRANULARITY_DAY;
    }

    public function type()
    {
        return STATISTIC_TYPE_FLOW;
    }

    public function span()
    {
        return 4;
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
        return (int) $this->app['db']->select('COUNT(uid)')->from('Users')
            ->where([['created_at', U::unixToDb($end), '<='], ['created_at', $start, '>=']])->scalar();
    }
}
