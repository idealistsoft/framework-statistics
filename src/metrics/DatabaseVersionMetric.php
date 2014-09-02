<?php

namespace app\statistics\metrics;

use infuse\Database;

class DatabaseVersionMetric extends AbstractStat
{
    public function name()
    {
        return 'Database Version';
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
        $query = Database::sql( 'SELECT VERSION()' );

        return $query->fetchColumn( 0 );
    }
}