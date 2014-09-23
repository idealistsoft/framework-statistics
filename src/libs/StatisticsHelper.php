<?php

/**
 * @package infuse\framework
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @version 0.1.16
 * @copyright 2013 Jared King
 * @license MIT
 */

namespace app\statistics\libs;

use infuse\Inflector;

use App;

class StatisticsHelper
{
    /**
	 * Returns all the classes of available metrics
	 *
	 * @param App $app
	 *
	 * @return array(AbstractStat)
	 */
    public static function metricClasses(App $app)
    {
        $return = [];

        $metrics = (array) $app[ 'config' ]->get( 'statistics.metrics' );
        foreach ($metrics as $metric) {
            $className = '\\app\\statistics\\metrics\\' . $metric . 'Metric';
            $return[] = new $className( $app );
        }

        return $return;
    }

    /**
	 * Captures each metric that needs to be captured at this time
	 *
	 * @param App $app
	 *
	 * @return boolean
	 */
    public static function captureMetrics(App $app)
    {
        $classes = self::metricClasses( $app );

        $success = true;

        foreach ($classes as $metric) {
            if( $metric->needsToBeCaptured() )
                $success = $metric->savePeriod( 1 ) && $success;
        }

        return $success;
    }

    /**
	 * Backfills all the metrics for N previous periods
	 *
	 * @param int $n number of previous periods to backfill
	 * @param App $app
	 *
	 * @return boolean
	 */
    public static function backfillMetrics($n, App $app)
    {
        $classes = self::metricClasses( $app );

        $success = true;

        foreach ($classes as $metric) {
            if( !$metric->shouldBeCaptured() )
                continue;

            $i = 1;
            while ($i <= $n) {
                $success = $metric->savePeriod( $i ) && $success;

                $i++;
            }
        }

        return $success;
    }

    /**
	 * Looks up the metric class for a given key
	 *
	 * @param string $key
	 * @param App $app
	 *
	 * @return AbstractStat|false
	 */
    public static function getClassForKey($key, App $app)
    {
        $className = '\\app\\statistics\\metrics\\' . Inflector::camelize($key) . 'Metric';

        if (class_exists($className))
            return new $className($app);

        return false;
    }
}
