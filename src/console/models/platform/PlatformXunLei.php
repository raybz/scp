<?php

namespace console\models\platform;

use common\definitions\UserIsAdult;

class PlatformXunLei extends Platform
{
    protected static function uniformPayData($newParam)
    {
        $pay_data = array(
            'uid' => $newParam['user'] ?? null,
            'platform' => 'xunlei',
            'gkey' => 'tlzj',
            'server_id' => str_replace('s', '', $newParam['server']  ?? 0),
            'time' => $newParam['time'] ?? null,
            'order_id' => $newParam['orderid'] ?? null,
            'coins' => $newParam['gold'] ?? 0,
            'money' => $newParam['money'] ?? 0,
        );

        return $pay_data;
    }

    protected static function uniformLoginData($newParam)
    {
        $login_type = 'web';
        if (isset($newParam['clienttype']) && $newParam['clienttype'] == 3) {
            $login_type = 'pc';
        } elseif (isset($newParam['clienttype']) && $newParam['clienttype'] == 2) {
            $login_type = 'box';
        }
        $back_url = 'http://niu.xunlei.com';
        $login_data = array(
            'uid' => $newParam['username'] ?? null,
            'platform' => 'xunlei',
            'gkey' => 'tlzj',
            'server_id' => str_replace('s', '', $newParam['serverid']  ?? 0),
            'time' => $newParam['time'] ?? null,
            'is_adult' => $newParam['cm'] ?? UserIsAdult::OTHER,
            'back_url' => $back_url,
            'type' => $login_type,
            'sign' => strtolower($newParam['token'] ?? ''),
        );

        return $login_data;
    }
}
