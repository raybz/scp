<?php

namespace console\models\platform;

class Platform4399 extends Platform
{
    protected static function uniformPayData($newParam)
    {
        $param_list = explode('|', $newParam['p']);
        $pay_num = array_shift($param_list);
        $pay_to_user = array_shift($param_list);
        $pay_gold = array_shift($param_list);
        $time = array_shift($param_list);
        $flag = array_shift($param_list);
        $pay_rmb = array_shift($param_list);
        $channel = array_shift($param_list);

        $pay_data = array(
            'uid' => $pay_to_user,
            'platform' => '4399',
            'gkey' => 'tlzj',
            'server_id' => 's'.str_replace('s', '', $newParam['serverid']),
            'time' => $time,
            'order_id' => $pay_num,
            'coins' => $pay_gold,
            'money' => $pay_rmb,
        );

        return $pay_data;
    }

    protected static function uniformLoginData($newParam)
    {
        $back_url = 'http://my.4399.com/yxtlzj/';
        $login_type = 'web';
        if (isset($newParam['client']) && $newParam['client'] == 1) {
            $login_type = 'pc';
        }
        $login_data = array(
            'uid' => $newParam['username'],
            'platform' => '4399',
            'gkey' => 'tlzj',
            'server_id' => 's' . str_replace('s', '', $newParam['serverid']),
            'time' => $newParam['time'],
            'is_adult' => $newParam['cm'],
            'back_url' => urldecode($back_url),
            'type' => $login_type,
            'sign' => strtolower($newParam['flag']),
        );

        return $login_data;
    }
}
