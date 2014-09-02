<?php

namespace app\statistics\metrics;

use infuse\Database;

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
        return (int) Database::select(
            'Users',
            'count(uid)',
            [
                'where' => [
                    'created_at >= ' . $start,
                    'created_at <= ' . $end ],
                'single' => true ] );
    }
}
