<?php

namespace app\statistics\metrics;

class FrameworkVersionMetric extends AbstractStat
{
    public function name()
    {
        return 'Framework Version';
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
        // load composer installed.json
        $installed = json_decode(file_get_contents(INFUSE_BASE_DIR . '/vendor/composer/installed.json'));

        // returns the version of the core framework package
        foreach ($installed as $package) {
            if ($package->name == 'idealistsoft/framework-bootstrap')
                return $package->version;
        }

        return '';
    }
}
