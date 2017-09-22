<?php

namespace console\models\platform;

class Platform37 extends Platform
{
    protected static function uniformPayData($newParam)
    {
        $pay_data = array(
            'uid' => $newParam['user_name'] ?? null,
            'platform' => '37wan',
            'gkey' => 'tlzj',
            'server_id' => str_replace('s', '', $newParam['server_id']  ?? 0),
            'time' => $newParam['time'] ?? null,
            'order_id' => $newParam['order_id'] ?? null,
            'coins' => $newParam['coin'] ?? 0,
            'money' => $newParam['money'] ?? 0,
        );

        return $pay_data;
    }

    protected static function uniformLoginData($newParam)
    {
        $login_type = 'web';
        if (isset($newParam['client']) && $newParam['client'] == 2 || isset($newParam['client']) && $newParam['client'] == 3) {
            $login_type = 'pc';
        } elseif (isset($newParam['client']) && $newParam['client'] == 4) {
            $login_type = 'box';
        }

        $is_adult = 1;
        if (isset($newParam['is_adult']) && $newParam['is_adult'] == -1) {
            $is_adult = 0;
        } elseif (isset($newParam['is_adult']) && $newParam['is_adult'] == 0) {
            $is_adult = 2;
        }
        $back_url = 'http://www.37.com/';

        $login_data = array(
            'uid' => $newParam['user_name'] ?? null,
            'platform' => '37wan',
            'gkey' => 'tlzj',
            'server_id' => str_replace('s', '', $newParam['server_id']  ?? 0),
            'time' => $newParam['time'] ?? null,
            'is_adult' => $is_adult,
            'back_url' => urldecode($back_url),
            'type' => $login_type,
            'sign' => strtolower($newParam['sign']),
        );

        return $login_data;
    }
}