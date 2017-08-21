<?php

namespace console\models\platform;

class PlatformSoGou extends Platform
{
    protected static function uniformPayData($newParam)
    {
        $pay_data = array(
            'uid' => $newParam['uid'],
            'platform' => 'sogou',
            'gkey' => 'tlzj',
            'server_id' => $newParam['sid']  ?? 0,
            'time' => strtotime($newParam['time']),
            'order_id' => $newParam['oid'],
            'coins' => $newParam['amount2'],
            'money' => $newParam['amount1'],
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
            'uid' => $newParam['uid'],
            'platform' => 'sogou',
            'gkey' => 'tlzj',
            'server_id' => $newParam['sid']  ?? 0,
            'time' => strtotime($newParam['time']),
            'is_adult' => $newParam['cm'] == 2 ? 1: ($newParam['cm'] == 1 ? 2 : 0),
            'back_url' => $back_url,
            'type' => 'web',
            'sign' => $newParam['auth'],
        );

        return $login_data;
    }
}