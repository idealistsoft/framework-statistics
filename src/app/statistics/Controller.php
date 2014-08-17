<?php

/**
 * @package infuse\framework
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @version 0.1.16
 * @copyright 2013 Jared King
 * @license MIT
 */
 
namespace app\statistics;

use infuse\Util;

use App;
use app\statistics\libs\StatisticsHelper;

class Controller
{
	public static $properties = [
		'models' => [
			'Statistic' ],
		'defaultHistoryMetric' => 'users.numUsers',
		'routes' => [
			'get /admin/statistics' => 'adminHome',
			'get /admin/statistics/history' => 'adminHistoryDefault',
			'get /admin/statistics/history/:metric' => 'adminHistory',
			'get /statistics/:metric' => 'metric',
			'get /statistics/captureMetrics' => 'captureMetrics',
			'get /statistics/backfill' => 'backfill'
		]
	];

	public static $hasAdminView;

	public static $metricOrdering = [
		'Signup Funnel' => [
			'SignupsToday'
		],
		'Usage' => [
			'Users'
		],
		'Site' => [
			'SiteStatus',
			'SiteVersion',
			'PhpVersion',
			'SiteMode',
			'SiteSession',
		],
		'Database' => [
			'DatabaseSize',
			'DatabaseVersion',
			'DatabaseTables'
		]
	];

	private $app;

	function __construct( App $app )
	{
		$this->app = $app;
	}

	function adminHome( $req, $res )
	{
		if( !$this->app[ 'user' ]->isAdmin() )
			return $res->setCode( 401 );

		$metrics = [];
		$chartData = [];
		$chartGranularities = [];
		$chartLoadedIntervals = [];

		$start = -7;
		$end = 0;

		foreach( self::$metricOrdering as $section => $metricClasses )
		{
			foreach( $metricClasses as $className )
			{
				$className = '\\app\\statistics\\metrics\\' . $className . 'Metric';

				$metric = new $className( $this->app );

				$k = $metric->key();

				Util::array_set( $metrics, $section . '.' . $k, $metric->toArray() );

				if( $metric->hasChart() ) {
					$chartData[ $k ] = $metric->values();
					$gName = $metric::$granularityNames[ $metric->granularity() ];
					$chartGranularities[ $k ] = $gName;
					$chartLoadedIntervals[ $k ] = 'last-7-' . $gName . 's';
				}
			}
		}
		
		$res->render( 'admin/index', [
			'metrics' => $metrics,
			'chartNames' => array_keys( $chartData ),
			'chartData' => $chartData,
			'chartGranularities' => $chartGranularities,
			'chartLoadedIntervals' => $chartLoadedIntervals,
			'adminViewsDir' => INFUSE_APP_DIR . '/admin/views',
			'title' => 'Statistics'
		] );
	}

	function metric( $req, $res )
	{
		if( !$req->isJson() )
			return $res->setCode( 406 );

		if( !$this->app[ 'user' ]->isAdmin() )
			return $res->setCode( 401 );

		$metric = StatisticsHelper::getClassForKey( $req->params( 'metric' ), $this->app );

		if( !$metric )
			return $res->setCode( 404 );

		return $res->setBodyJson( [
			'data' => $metric->values( $req->query( 'start' ), $req->query( 'end' ) ) ] );
	}

	function cron( $command )
	{
		if( $command == 'capture-snapshot' || $command == 'capture-metrics' ) {
			if( StatisticsHelper::captureMetrics( $this->app ) )
			{
				echo "Successfully captured metrics\n";
				return true;
			}
			else
			{
				echo "Failed to capture metrics\n";
				return false;
			}
		}
	}

	function captureMetrics( $req, $res )
	{
		if( !$req->isCli() )
			return $res->setCode( 404 );

		if( StatisticsHelper::captureMetrics( $this->app ) )
		{
			echo "Successfully captured metrics\n";
			return true;
		}
		else
		{
			echo "Failed to capture metrics\n";
			return false;
		}
	}

	function backfill( $req, $res )
	{
		if( !$req->isCli() )
			return $res->setCode( 404 );

		$n = (int)$req->cliArgs( 2 );

		if( StatisticsHelper::backfillMetrics( $n, $this->app ) )
		{
			echo "Successfully backfilled metrics\n";
			return true;
		}
		else
		{
			echo "Failed to backfill metrics\n";
			return false;
		}
	}
}