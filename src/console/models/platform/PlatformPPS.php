<?php

namespace console\models\platform;

use common\definitions\UserIsAdult;

class PlatformPPS extends Platform
{
    protected static function uniformPayData($newParam)
    {
        $pay_data = array(
            'uid' => $newParam['user_id'],
            'platform' => 'pps',
            'gkey' => 'tlzj',
            'server_id' => str_replace('s', '', $newParam['server_id']  ?? 0),
            'time' => $newParam['time'],
            'order_id' => $newParam['order_id'],
            'coins' => intval($newParam['money']) * 100,
            'money' => $newParam['money'],
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
            'uid' => $newParam['user_id'],
            'platform' => 'pps',
            'gkey' => 'tlzj',
            'server_id' => str_replace('s', '', $newParam['server_id'] ?? 0),
            'time' => $newParam['time'],
            'is_adult' => $newParam['is_adult'] ?? UserIsAdult::OTHER,
            'back_url' => '',
            'type' => $login_type,
            'sign' => $newParam['sign'],
        );

        return $login_data;
    }
}