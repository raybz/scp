<?php

namespace console\models\platform;

use common\models\Game;
use common\models\Server;
use common\models\Payment;
use common\models\User;
use console\models\LoginLogTable;
use yii\base\Model;

class Platform extends Model
{
    const EVENT_BEFORE_CREATE = 'before_create';
    const EVENT_AFTER_CREATE =  'after_create';

    public static $url_param;

    protected static $id;
    protected static $user_id;

    protected static function uniformPayData($param)
    {
        return $param;
    }

    protected static function uniformLoginData($param)
    {
        return $param;
    }

    protected static function VerifyPayData()
    {
        $need = [
            'uid',
            'platform',
            'gkey',
            'server_id',
            'time',
            'order_id',
            'coins',
            'money',
        ];
        $uData = self::uniformPayData(self::paramData());
        foreach ($need as $item) {
            if (array_key_exists($item, $uData)) {
                continue;
            }

            return null;
        }

        return $uData;
    }

    protected static function VerifyLoginData()
    {
        $need = [
            'uid',
            'platform',
            'gkey',
            'server_id',
            'time',
            'is_adult',
            'back_url',
            'type',
            'sign',
        ];
        $uData = self::uniformLoginData(self::paramData());
        foreach ($need as $item) {
            if (array_key_exists($item, $uData)) {
                continue;
            }

            return null;
        }

        return $uData;
    }

    public static function savePay()
    {
        $data = static::VerifyPayData();
        if (!$data) {
            return ['', '', 'data error'];
        }
        $payObj = self::parse($data);
//var_dump($payObj);exit;
        try {
            $mod = new self;
            $mod->_eventBefore($payObj);
            $result = Payment::storeData($payObj);

            return $result;
        }catch (\Exception $e) {
            $error = $e->getMessage().$e->getFile().$e->getLine();
            var_dump($error);
            file_put_contents(\Yii::getAlias('@console').'/runtime/errors/pay_error.log', $error.$e->getTraceAsString().PHP_EOL, FILE_APPEND);

            return null;
        }
    }

    public static function saveLogin()
    {
        //是否存在
        $data = static::VerifyLoginData();
        if (!$data) {
            return ['', '', 'data error'];
        }
        $loginObj = self::parse($data);

        if (isset($loginObj->time) && $loginObj->time > 0 && isset($loginObj->uid) && $loginObj->uid) {
            try {
                LoginLogTable::$month = date('Ym', $loginObj->time);
                $mod = new self;
                $mod->_eventBefore($loginObj);
                LoginLogTable::newTable($loginObj->time);
                $result = LoginLogTable::storeData($loginObj);
                if ($result) {
                    static::$id = $result;
                    $mod->_eventAfter();

                    return ['new', $result, static::$user_id];
                }

                return ['old', '', ''];

            } catch (\Exception $e) {
                $error = $e->getMessage().$e->getFile().$e->getLine().PHP_EOL;
                var_dump($error);
                file_put_contents(\Yii::getAlias('@console').'/runtime/errors/login_error.log', $error.$e->getTraceAsString().PHP_EOL, FILE_APPEND);
                return ['', '', ''];
            }
        }

        return ['', '', ''];
    }

    protected function _eventBefore($data)
    {
//        $this->on(self::EVENT_BEFORE_CREATE, [$this, '_eventAddLoginTable'], $data);
        $this->on(self::EVENT_BEFORE_CREATE, [$this, '_eventAddPlatform'], $data);
        $this->on(self::EVENT_BEFORE_CREATE, [$this, '_eventAddServer'], $data);
        $this->trigger(self::EVENT_BEFORE_CREATE);
    }

    protected function _eventAfter()
    {
        $this->on(self::EVENT_AFTER_CREATE, [$this, '_eventSaveUser']);
        $this->trigger(self::EVENT_AFTER_CREATE);
    }

    protected function _eventSaveUser()
    {
        $user = LoginLogTable::findOne(static::$id);
        static::$user_id = User::saveUser($user);
    }

    protected function _eventAddLoginTable($event){
        $time = $event->data;
        LoginLogTable::newTable($time->time);
    }

    protected function _eventAddPlatform($event){
        $platform = new \common\models\Platform();
        $platform->storeData($event->data);
    }

    protected function _eventAddServer($event){
        $server = new Server();
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
        $paramArr = explode('&', strtolower($url_param));
        $newParam = [];
        foreach ($paramArr as $val) {
            if (strpos($val, '=')) {
                $p = explode('=', $val);
                if (trim($p[0], ' ') == 'back_url') {
                    $p[1] = urldecode($p[1]);
                }
                if (trim($p[0], ' ') == 'server_id') {
                    $p[1] = str_replace('s', '', $p[1]);
                }
                if (trim($p[0], ' ') == 'gkey') {
                    if ($p[1] == 'tl') {
                        $p[1] = 'tlzj';
                    }
                }
                $newParam[trim($p[0], ' ')] = $p[1];
            }
        }
//var_dump($newParam);
        return $newParam;
    }
}
