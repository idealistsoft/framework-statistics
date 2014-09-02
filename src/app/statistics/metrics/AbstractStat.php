<?php

namespace app\statistics\metrics;

use infuse\Database;
use infuse\Util;

use App;
use app\statistics\models\Statistic;

define( 'STATISTIC_GRANULARITY_DAY', 1 );
define( 'STATISTIC_GRANULARITY_WEEK', 2 );
define( 'STATISTIC_GRANULARITY_MONTH', 3 );
define( 'STATISTIC_GRANULARITY_YEAR', 4 );

// totals, i.e. total revenue, total users
define( 'STATISTIC_TYPE_VOLUME', 1 );
// units/granularity, i.e. revenue this month, new users
define( 'STATISTIC_TYPE_FLOW', 2 );
// just displays a piece of information, usually a string instead of number
define( 'STATISTIC_INFORMATION', 3 );

abstract class AbstractStat
{
    public static $granularityNames = [
        STATISTIC_GRANULARITY_DAY => 'day',
        STATISTIC_GRANULARITY_WEEK => 'week',
        STATISTIC_GRANULARITY_MONTH => 'month',
        STATISTIC_GRANULARITY_YEAR => 'year'
    ];

    protected $app;
    private $values = [];

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    ////////////////////////////
    // STATISTIC PROPERTIES
    ////////////////////////////

    /**
	 * Gets the proper name of the metric.
	 *
	 * @return string
	 */
    abstract public function name();

    /**
	 * Gets the granularity of the metric.
	 *
	 * @return int
	 */
    abstract public function granularity();

    /**
	 * Gets the type of statitics
	 *
	 * @return int
	 */
    abstract public function type();

    /**
	 * Returns true if the metric has a chart.
	 *
	 * @return boolean
	 */
    abstract public function hasChart();

    /**
	 * Returns true if a delta can be computed for the metric.
	 *
	 * @return boolean
	 */
    abstract public function hasDelta();

    /**
	 * Returns true if the metric should be captured
	 *
	 * @return boolean
	 */
    abstract public function shouldBeCaptured();

    /**
	 * Gets the prefix for metric values being displayed (i.e. '$')
	 *
	 * @return string
	 */
    public function prefix()
    {
        return '';
    }

    /**
	 * Gets the suffix for metric values being displayed (i.e. ' users')
	 *
	 * @return string
	 */
    public function suffix()
    {
        return '';
    }

    /**
	 * Returns how many columns the metric should span (out of 12)
	 *
	 * @return int
	 */
    public function span()
    {
        return 4;
    }

    /**
	 * Computes the canonical name used for this metric
	 *
	 * @return string
	 */
    public function key()
    {
        return Util::seoify( $this->name() );
    }

    ////////////////////////////
    // METRIC COMPUTATIONS
    ////////////////////////////

    /**
	 * Computes (and caches) the value of the metric over the specified period
	 *
	 * @param array $period [ start, end ]
	 *
	 * @return string|number
	 */
    public function computeValue(array $period = [])
    {
        if( !isset( $period[ 'start' ] ) || !isset( $period[ 'end' ] ) )
            $period = $this->nthPreviousPeriod();

        $start = $period[ 'start' ];
        $end = $period[ 'end' ];

        // cache computed values
        if( !isset( $this->values[ "$start.$end" ] ) )
            $this->values[ "$start.$end" ] = $this->value( $start, $end );

        return $this->values[ "$start.$end" ];
    }

    /**
	 * Computes (no cache) the value of the metric over the specified period
	 *
	 * @param int $start starting timestamp for the period
	 * @param int $end ending timestamp for the period
	 *
	 * @return string|number
	 */
    abstract public function value( $start, $end );

    /**
	 * Calculates the delta between the current value and the previous value
	 *
	 * @return number
	 */
    public function computeDelta()
    {
        if( !$this->hasDelta() )

            return false;

        // computes this and the most recently completed period
        $thisPeriod = $this->nthPreviousPeriod();
        $mostRecentPeriod = $this->nthPreviousPeriod( 1 );

        $current = $this->computeValue( $thisPeriod );
        $previous = $this->computeValue( $mostRecentPeriod );

        return $current - $previous;
    }

    ////////////////////////////
    // HISTORY LOOKUPS
    ////////////////////////////

    /**
	 * Looks up all stored values between two timestamps. If more points are available
	 * than requested, then the points will be summed up according to equally spaced
	 * intervals.
	 *
	 * @param int $start
	 * @param int $end
	 * @param int $maxPoints maximum number of points to return
	 *
	 * @return array
	 */
    public function values($start = -6, $end = 0)
    {
        $start = (int) $start;
        $end = (int) $end;

        // go N points in the past
        if ($start < 0) {
            $granularity = $this->granularity();
            if ($granularity == STATISTIC_GRANULARITY_DAY) {
                $nPrevious = strtotime( $start . ' days' );
                $start = mktime( 0, 0, 0, date( 'm', $nPrevious ), date( 'd', $nPrevious ), date( 'Y', $nPrevious ) );
            } elseif ($granularity == STATISTIC_GRANULARITY_WEEK) {
                $nPrevious = strtotime( $start . ' weeks' );
                $start = mktime( 0, 0, 0, date( 'm', $nPrevious ), date( 'd', $nPrevious ), date( 'Y', $nPrevious ) );
            } elseif ($granularity == STATISTIC_GRANULARITY_MONTH) {
                $nPrevious = strtotime( $start . ' months' );
                $start = mktime( 0, 0, 0, date( 'm', $nPrevious ), 1, date( 'Y', $nPrevious ) );
            } elseif ($granularity == STATISTIC_GRANULARITY_YEAR) {
                $nPrevious = strtotime( $start . ' years' );
                $start = mktime( 0, 0, 0, 1, 1, date( 'Y', $nPrevious ) );
            }
        }

        // end should include the present value
        $includePresent = $end == 0 || $end - time() >= 0;
        if( $includePresent )
            $end = time();

        $values = Database::select(
            'Statistics',
            'day,val',
            [
                'where' => [
                    'metric' => $this->key(),
                    "ts >= '$start'",
                    "ts <= '$end'" ] ] );

        // add present point
        if( $includePresent )
            $values[] = [
                'day' => date( 'Y-m-d' ),
                'val' => $this->computeValue() ];

        return $values;
    }

    ////////////////////////////
    // HISTORY CAPTURE
    ////////////////////////////

    /**
	 * Checks if the metric needs a value captured for the newest completed period.
	 *
	 * @return boolean
	 */
    public function needsToBeCaptured()
    {
        if( !$this->shouldBeCaptured() )

            return false;

        // computes the most recently completed period
        $mostRecentPeriod = $this->nthPreviousPeriod( 1 );

        // computes the start date for the most recently completed period
        $latestStartDate = date( 'Y-m-d', $mostRecentPeriod[ 'start' ] );

        // check if the metric has been saved before
        return Statistic::totalRecords( [
            'metric' => $this->key(),
            'day' => $latestStartDate ] ) == 0;
    }

    /**
	 * Saves the Nth previous period. Creates the statistic entry
	 * if it does not exist, otherwise overwrites the previous value
	 *
	 * @param int $n nth previous period to save
	 *
	 * @return boolean
	 */
    public function savePeriod($n = 1)
    {
        if( !$this->shouldBeCaptured() || $n < 1 )

            return false;

        // compute the period in question
        $period = $this->nthPreviousPeriod( $n );

        // compute the value for that time period
        $value = $this->computeValue( $period );

        // generate the start date for the period
        $d = date( 'Y-m-d', $period[ 'start' ] );

        // save the value
        $stat = new Statistic( [ $this->key(), $d ] );
        if ( !$stat->exists() ) {
            $stat = new Statistic();

            return $stat->create( [
                'metric' => $this->key(),
                'day' => $d,
                'val' => $value ] );
        } else

            return $stat->set( 'val', $value );
    }

    ////////////////////////////
    // UTILITIES
    ////////////////////////////

    /**
	 * Bundles up some useful properties of the statistic for convenience.
	 *
	 * @return array
	 */
    public function toArray()
    {
        $name = $this->name();

        $value = $this->computeValue();
        $delta = $this->computeDelta();

        return [
            'name' => $name,
            'key' => $this->key(),
            'granularity' => $this->granularity(),
            'prefix' => $this->prefix(),
            'suffix' => $this->suffix(),
            'hasChart' => $this->hasChart(),
            'span' => $this->span(),
            'value' => $value,
            'abbreviated_value' => (is_numeric($value)) ? Util::number_abbreviate( round( $value, 2 ), 1 ) : $value,
            'delta' => $delta,
            'abbreviated_delta' => (is_numeric($delta)) ? Util::number_abbreviate( round( $delta, 2 ), 1 ) : $delta,
        ];
    }

    /**
	 * Returns the start and end timestamps for the nth-previous period
	 * since the current period. 0 will return the current period
	 *
	 * @param int $n
	 *
	 * @return array period: [ start, end ]
	 */
    public function nthPreviousPeriod($n = 0)
    {
        $n = max( 0, $n );

        // determine the interval string
        $interval = $this->interval( $this->granularity() );

        // calculate the start of the current period
        $start = $this->startOfDay( strtotime( 'this ' . $interval ) );

        // go back N periods
        $i = $n;
        while ($i > 0) {
            $start = $this->startOfDay( strtotime( '-1 ' . $interval, $start ) );
            $i--;
        }

        // the end will be the start + 1 period - 1 second
        $end = $this->endOfDay( strtotime( '+1 ' . $interval, $start ) - 1 );

        return [
            'start' => $start,
            'end' => $end ];
    }

    /**
	 * Gets the interval string for this metric based on the
	 * granularity
	 *
	 * @return string
	 */
    public function interval($granularity)
    {
        switch ($granularity) {
            case STATISTIC_GRANULARITY_DAY:
                return 'day';
            case STATISTIC_GRANULARITY_WEEK:
                return 'week';
            case STATISTIC_GRANULARITY_MONTH:
                return 'month';
            case STATISTIC_GRANULARITY_YEAR:
                return 'year';
        }
    }

    public function startOfDay($ts)
    {
        return floor( $ts / 86400 ) * 86400;
    }

    public function endOfDay($ts)
    {
        return ( floor( $ts / 86400 ) + 1 ) * 86400 - 1;
    }
}
