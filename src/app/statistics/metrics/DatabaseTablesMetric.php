<?php

namespace app\statistics\metrics;

use infuse\Database;

class DatabaseTablesMetric extends AbstractStat
{
    public function name()
    {
        return 'Database Tables';
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
        // calculate metric as of right now
        $query = Database::sql("SHOW table STATUS");

        $status = $query->fetchAll( \PDO::FETCH_ASSOC );

        return count( $status );
    }
}
