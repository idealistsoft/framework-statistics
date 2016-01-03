<?php

namespace app\statistics\metrics;

use Infuse\Utility as U;

class TotalUsersMetric extends AbstractStat
{
    public function name()
    {
        return 'Total Users';
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
            ->where('created_at', U::unixToDb($end), '<=')->scalar();
    }
}
