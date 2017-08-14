<?php

namespace console\models\platform;

use common\models\GamePlatformServer;
use common\models\Payment;
use console\models\LoginLogTable;
use yii\base\Model;

class Platform extends Model
{
    const EVENT_BEFORE_CREATE = 'before_create';

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

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $mod = new self;
            $mod->_eventBefore($payObj);
            $result = Payment::storeData($payObj);
            $transaction->commit();
            return $result;
        }catch (\Exception $e) {
            var_dump($e->getMessage());
            $transaction->rollBack();
            return null;
        }
    }

    public static function saveLogin()
    {
        $loginObj = self::parse(static::uniformLoginData(self::paramData()));
        $transaction = \Yii::$app->db->beginTransaction();
        if (isset($loginObj->time) && $loginObj->time > 0 && isset($loginObj->uid) && $loginObj->uid) {
            try {
                $mod = new self;
                $mod->_eventBefore($loginObj);
                $result = LoginLogTable::storeData($loginObj);
                $transaction->commit();

                return $result;
            } catch (\Exception $e) {
                var_dump($e->getMessage().$e->getFile().$e->getLine());
                $transaction->rollBack();

                return ['', '', ''];
            }
        }

        return ['', '', ''];
    }

    protected function _eventBefore($data)
    {
        $this->on(self::EVENT_BEFORE_CREATE, [$this, '_eventAddPlatform'], $data);
        $this->on(self::EVENT_BEFORE_CREATE, [$this, '_eventAddServer'], $data);
        $this->trigger(self::EVENT_BEFORE_CREATE);
    }

    protected function _eventAddPlatform($event){
        $platform = new \common\models\Platform();
        $platform->storeData($event->data);
    }

    protected function _eventAddServer($event){
        $server = new GamePlatformServer();
        $server->storeData($event->data);
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
            if (trim($p[0], ' ') == 'server_id') {
                $p[1] = str_replace('s', '', $p[1]);
            }
            $newParam[trim($p[0], ' ')] = $p[1];
        }

        return $newParam;
    }
}
