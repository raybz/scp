<?php

namespace console\models\platform;

class PlatformYY extends Platform
{
    protected static function uniformPayData($newParam)
    {
        $pay_data = array(
            'uid' => $newParam['account'],
            'platform' => 'yy',
            'gkey' => 'tlzj',
            'server_id' => str_replace('s', '', $newParam['server']),
            'time' => $newParam['time'],
            'order_id' => $newParam['orderid'],
            'coins' => $newParam['num'],
            'money' => $newParam['rmb'],
        );

        return $pay_data;
    }

    protected static function uniformLoginData($newParam)
    {
        $login_type = 'web';
        if ($newParam['client'] == 1) {
            $login_type = 'pc';
        } elseif ($newParam['client'] == 2) {
            $login_type = 'box';
        }
        $back_url = $newParam['backurl'] ?? '';
        $login_data = array(
            'uid' => $newParam['account'],
            'platform' => 'yy',
            'gkey' => 'tlzj',
            'server_id' => str_replace('s', '', $newParam['server']),
            'time' => $newParam['time'],
            'is_adult' => $newParam['fm'],
            'back_url' => $back_url,
            'type' => $login_type,
            'sign' => strtolower($newParam['sign']),
        );

        return $login_data;
    }
}

