<?php

namespace app\statistics\metrics;

use app\statistics\models\Statistic;

class DatabaseSizeMetric extends AbstractStat
{
    public function name()
    {
        return 'Database Size';
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
        return 12;
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

    public function suffix()
    {
        return 'B';
    }

    public function value($start, $end)
    {
        $status = $this->app['db']->raw('SHOW table STATUS')->all();

        // Calculate DB size by adding table size + index size:
        $dbsize = 0;
        foreach ($status as $row) {
            $dbsize += $row['Data_length'] + $row['Index_length'];
        }

        return $dbsize;
    }

    public function computeDelta()
    {
        // get metric from yesterday
        $yesterdaysDate = date('Y-m-d', strtotime('yesterday'));

        $stat = Statistic::findOne([
            'where' => [
                'metric' => $this->key(),
                'day' => $yesterdaysDate, ], ]);

        $previous = ($stat) ? $stat->val : 0;

        return $this->computeValue() - $previous;
    }
}
