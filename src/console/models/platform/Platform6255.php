<?php

namespace console\models\platform;

use common\definitions\UserIsAdult;

class Platform6255 extends Platform
{
    protected static function uniformPayData($newParam)
    {
        $pay_data = array(
            'uid' => $newParam['uid'] ?? null,
            'platform' => '6255',
            'gkey' => 'tlzj',
            'server_id' => $newParam['server']  ?? 0,
            'time' => $newParam['time'] ?? null,
            'order_id' => $newParam['order'] ?? null,
            'coins' => $newParam['coins'] ?? 0,
            'money' => $newParam['money'] / 100 ?? 0,
        );

        return $pay_data;
    }

    protected static function uniformLoginData($newParam)
    {
        $login_data = array(
            'uid' => $newParam['uid'] ?? null,
            'platform' => '6255',
            'gkey' => 'tlzj',
            'server_id' => $newParam['server']  ?? 0,
            'time' => $newParam['time'] ?? null,
            'is_adult' => $newParam['is_adult'] ?? UserIsAdult::OTHER,
            'back_url' => urldecode($newParam['back_url'] ?? ''),
            'type' => $newParam['type'] ?? '',
            'sign' => strtolower($newParam['sign'] ?? ''),
        );

        return $login_data;
    }

    protected static function paramData()
    {
        return json_decode(json_encode(static::$url_param), true);
    }
}
