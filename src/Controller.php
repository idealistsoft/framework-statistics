<?php

/**
 * @author Jared King <j@jaredtking.com>
 *
 * @link http://jaredtking.com
 *
 * @version 1.0.0
 *
 * @copyright 2014 Jared King
 * @license MIT
 */
namespace app\statistics;

use Infuse\HasApp;
use Infuse\Utility as U;
use Infuse\View;
use app\statistics\libs\StatisticsHelper;

class Controller
{
    use HasApp;

    public static $properties = [
        'models' => [
            'Statistic', ],
        'defaultHistoryMetric' => 'users.numUsers',
    ];

    public static $hasAdminView;

    public static $viewsDir;

    public function __construct()
    {
        self::$viewsDir = __DIR__.'/views/';
    }

    public function middleware($req, $res)
    {
        $this->app->get('/admin/statistics', ['statistics\\Controller', 'adminHome'])
                  ->get('/admin/statistics/history', ['statistics\\Controller', 'adminHistoryDefault'])
                  ->get('/admin/statistics/history/:metric', ['statistics\\Controller', 'adminHistory'])
                  ->get('/statistics/:metric', ['statistics\\Controller', 'metric'])
                  ->get('/statistics/captureMetrics', ['statistics\\Controller', 'captureMetrics'])
                  ->get('/statistics/backfill', ['statistics\\Controller', 'backfill']);
    }

    public function adminHome($req, $res)
    {
        if (!$this->app['user']->isAdmin()) {
            return $res->redirect('/login?redir='.urlencode($req->basePath().$req->path()));
        }

        $metrics = [];
        $chartData = [];
        $chartGranularities = [];
        $chartLoadedIntervals = [];

        $start = -7;
        $end = 0;

        $dashboard = (array) $this->app[ 'config' ]->get('statistics.dashboard');
        foreach ($dashboard as $section => $metricClasses) {
            foreach ($metricClasses as $className) {
                $className = '\\app\\statistics\\metrics\\'.$className.'Metric';

                $metric = new $className($this->app);

                $k = $metric->key();

                U::array_set($metrics, $section.'.'.$k, $metric->toArray());

                if ($metric->hasChart()) {
                    $chartData[ $k ] = $metric->values();
                    $gName = $metric::$granularityNames[ $metric->granularity() ];
                    $chartGranularities[ $k ] = $gName;
                    $chartLoadedIntervals[ $k ] = 'last-7-'.$gName.'s';
                }
            }
        }

        return new View('admin/index', [
            'metrics' => $metrics,
            'chartNames' => array_keys($chartData),
            'chartData' => $chartData,
            'chartGranularities' => $chartGranularities,
            'chartLoadedIntervals' => $chartLoadedIntervals,
            'title' => 'Statistics',
        ]);
    }

    public function metric($req, $res)
    {
        if (!$req->isJson()) {
            return $res->setCode(415);
        }

        if (!$this->app[ 'user' ]->isAdmin()) {
            return $res->setCode(404);
        }

        $metric = StatisticsHelper::getClassForKey($req->params('metric'), $this->app);

        if (!$metric) {
            return $res->setCode(404);
        }

        return $res->json([
            'data' => $metric->values($req->query('start'), $req->query('end')), ]);
    }

    public function captureMetrics($req = null, $res = null)
    {
        if ($req && !$req->isCli()) {
            return $res->setCode(404);
        }

        if (StatisticsHelper::captureMetrics($this->app)) {
            echo "Successfully captured metrics\n";

            return true;
        } else {
            echo "Failed to capture metrics\n";

            return false;
        }
    }

    public function backfill($req, $res)
    {
        if (!$req->isCli()) {
            return $res->setCode(404);
        }

        $n = (int) $req->cliArgs(2);

        if (StatisticsHelper::backfillMetrics($n, $this->app)) {
            echo "Successfully backfilled metrics\n";

            return true;
        } else {
            echo "Failed to backfill metrics\n";

            return false;
        }
    }
}
