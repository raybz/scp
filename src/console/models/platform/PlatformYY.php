<?php

namespace console\models\platform;

use common\definitions\UserIsAdult;

class PlatformYY extends Platform
{
    protected static function uniformPayData($newParam)
    {
        $pay_data = array(
            'uid' => $newParam['account'] ?? null,
            'platform' => 'yy',
            'gkey' => 'tlzj',
            'server_id' => str_replace('s', '', $newParam['server']  ?? 0),
            'time' => $newParam['time'] ?? null,
            'order_id' => $newParam['orderid'] ?? null,
            'coins' => $newParam['num'] ?? 0,
            'money' => $newParam['rmb'] ?? 0,
        );

        return $pay_data;
    }

    protected static function uniformLoginData($newParam)
    {
        $login_type = 'web';
        if (isset($newParam['client']) && $newParam['client'] == 1) {
            $login_type = 'pc';
        } elseif (isset($newParam['client']) && $newParam['client'] == 2) {
            $login_type = 'box';
        }
        $back_url = $newParam['backurl'] ?? '';
        $login_data = array(
            'uid' => $newParam['account'] ?? null,
            'platform' => 'yy',
            'gkey' => 'tlzj',
            'server_id' => str_replace('s', '', $newParam['server']  ?? 0),
            'time' => $newParam['time'] ?? null,
            'is_adult' => $newParam['fm'] ?? UserIsAdult::OTHER,
            'back_url' => urldecode($back_url),
            'type' => $login_type,
            'sign' => strtolower($newParam['sign'] ?? ''),
        );

        return $login_data;
    }
}

