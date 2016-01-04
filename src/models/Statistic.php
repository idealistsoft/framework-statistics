<?php

namespace app\statistics\models;

use Pulsar\ACLModel;
use Pulsar\Model;

class Statistic extends ACLModel
{
    public static $properties = [
        'metric' => [
            'type' => 'string', ],
        'day' => [
            'type' => 'number', ],
        'val' => [
            'type' => 'string', ],
        'ts' => [
            'type' => 'date', ],
    ];

    public static function idProperty()
    {
        return ['metric', 'day'];
    }

    protected function hasPermission($permission, Model $requester)
    {
        return true;
    }

    public function preCreateHook(&$data)
    {
        // calculate timestamp from supplied date
        if (isset($data[ 'day' ])) {
            $date = explode('-', $data[ 'day' ]);
            list($year, $month, $day) = $date;

            $data[ 'ts' ] = mktime(0, 0, 0, $month, $day, $year);
        }

        return true;
    }
}
