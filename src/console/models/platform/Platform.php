<?php

namespace console\models\platform;

use common\models\Game;
use common\models\Payment;
use console\models\LoginLogTable;

class Platform
{
    public static $url_param;

    protected static function uniformPayData($param)
    {
        return $param;
    }

    protected static function uniformLoginData($param)
    {
        return $param;
    }

    public static function savePay()
    {
        $payObj = self::parse(static::uniformPayData(self::paramData()));
        //添加平台
        \common\models\Platform::storeData($payObj);
        $result = Payment::storeData($payObj);

        return $result;
    }

    public static function saveLogin()
    {
        $loginObj = self::parse(static::uniformLoginData(self::paramData()));

        if (isset($loginObj->time) && $loginObj->time > 0 && isset($loginObj->uid) && $loginObj->uid) {
            //添加平台
            \common\models\Platform::storeData($loginObj);

            LoginLogTable::newTable($loginObj->time);
            $result = LoginLogTable::storeData($loginObj);

            return $result;
        }

        return ['', '', ''];
    }

    protected static function parse($paramArr)
    {
        $obj = self::createObj();

        foreach (array_filter(array_keys($paramArr), 'is_string') as $key) {
            $obj->{$key} = $paramArr[$key];
        }

        return $obj;
    }

    protected static function createObj()
    {
        return new \stdClass();
    }

    protected static function paramData()
    {
        $url_param = strpos(self::$url_param, '=') ? self::$url_param : urldecode(self::$url_param);
        $paramArr = explode('&', $url_param);
        $newParam = [];
        foreach ($paramArr as $val) {
            $p = explode('=', $val);
            if (trim($p[0], ' ') == 'back_url') {
                $p[1] = urldecode($p[1]);
            }
            $newParam[trim($p[0], ' ')] = $p[1];
        }

        return $newParam;
    }
}
