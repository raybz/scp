<?php

namespace console\models\platform;

class PlatformXunLei extends Platform
{
    protected static function uniformPayData($newParam)
    {
        $pay_data = array(
            'uid' => $newParam['user'],
            'platform' => 'xunlei',
            'gkey' => 'tlzj',
            'server_id' => 's'.str_replace('s', '', $newParam['server']),
            'time' => $newParam['time'],
            'order_id' => $newParam['orderid'],
            'coins' => $newParam['gold'],
            'money' => $newParam['money'],
        );

        return $pay_data;
    }

    protected static function uniformLoginData($newParam)
    {
        $login_type = 'web';
        if ($newParam['clienttype'] == 3) {
            $login_type = 'pc';
        } elseif ($newParam['clienttype'] == 2) {
            $login_type = 'box';
        }
        $back_url = 'http://niu.xunlei.com';
        $login_data = array(
            'uid' => $newParam['username'],
            'platform' => 'xunlei',
            'gkey' => 'tlzj',
            'server_id' => 's' . str_replace('s', '', $newParam['serverid']),
            'time' => $newParam['time'],
            'is_adult' => $newParam['cm'],
            'back_url' => $back_url,
            'type' => $login_type,
            'sign' => strtolower($newParam['token']),
        );

        return $login_data;
    }
}
