<?php

namespace console\models\platform;

use common\definitions\UserIsAdult;

class PlatformSoGou extends Platform
{
    protected static function uniformPayData($newParam)
    {
        $pay_data = array(
            'uid' => $newParam['uid'] ?? null,
            'platform' => 'sogou',
            'gkey' => 'tlzj',
            'server_id' => $newParam['sid']  ?? 0,
            'time' => isset($newParam['time']) ? strtotime($newParam['time']) : null,
            'order_id' => $newParam['oid'] ?? null,
            'coins' => $newParam['amount2'] ?? 0,
            'money' => $newParam['amount1'] ?? 0,
        );

        return $pay_data;
    }

    protected static function uniformLoginData($newParam)
    {
        if (!isset($newParam['gid']) || !isset($newParam['sid'])) {
            $back_url = "http://wan.sogou.com";
        } else {
            $back_url = "http://wan.sogou.com/{$newParam['gid']}/?sid={$newParam['sid']}";
        }
        $login_data = array(
            'uid' => $newParam['uid'] ?? null,
            'platform' => 'sogou',
            'gkey' => 'tlzj',
            'server_id' => $newParam['sid']  ?? 0,
            'time' => isset($newParam['time']) ? strtotime($newParam['time']) : null,
            'is_adult' => isset($newParam['cm']) ? ($newParam['cm'] == 2 ? 1: ($newParam['cm'] == 1 ? 2 : 0)) : UserIsAdult::OTHER,
            'back_url' => $back_url,
            'type' => 'web',
            'sign' => $newParam['auth'] ?? '',
        );

        return $login_data;
    }
}