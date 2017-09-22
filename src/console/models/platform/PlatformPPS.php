<?php

namespace console\models\platform;

use common\definitions\UserIsAdult;

class PlatformPPS extends Platform
{
    protected static function uniformPayData($newParam)
    {
        $pay_data = array(
            'uid' => $newParam['user_id'] ?? null,
            'platform' => 'pps',
            'gkey' => 'tlzj',
            'server_id' => str_replace('s', '', $newParam['server_id']  ?? 0),
            'time' => $newParam['time'] ?? null,
            'order_id' => $newParam['order_id'] ?? null,
            'coins' => intval($newParam['money'] ?? 0) * 100,
            'money' => $newParam['money'] ?? 0,
        );

        return $pay_data;
    }

    protected static function uniformLoginData($newParam)
    {
        $login_type = 'web';
        if (isset($newParam['is_client']) && $newParam['is_client'] == 1) {
            $login_type = 'pc';
        }
        $login_data = array(
            'uid' => $newParam['user_id'] ?? null,
            'platform' => 'pps',
            'gkey' => 'tlzj',
            'server_id' => str_replace('s', '', $newParam['server_id'] ?? 0),
            'time' => $newParam['time'] ?? null,
            'is_adult' => $newParam['is_adult'] ?? UserIsAdult::OTHER,
            'back_url' => '',
            'type' => $login_type,
            'sign' => $newParam['sign'] ?? '',
        );

        return $login_data;
    }
}