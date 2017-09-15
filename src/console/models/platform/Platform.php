<?php

namespace console\models\platform;

use common\definitions\UserIsAdult;
use common\models\Payment;
use common\models\Server;
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
        $pay_data = array(
            'uid' => $param['uid'] ?? null,
            'platform' => $param['platform'] ?? null,
            'gkey' => 'tlzj',
            'server_id' => $param['server_id']  ?? 0,
            'time' => $param['time'] ?? null,
            'order_id' => $param['order_id'] ?? null,
            'coins' => $param['coins'] ?? 0,
            'money' => $param['money'] ?? 0,
        );

        return $pay_data;
    }

    protected static function uniformLoginData($param)
    {
        $login_data = array(
            'uid' => $param['uid'] ?? null,
            'platform' => $param['platform'] ?? null,
            'gkey' => 'tlzj',
            'server_id' => $param['server']  ?? 0,
            'time' => $param['time'] ?? null,
            'is_adult' => $param['is_adult'] ?? UserIsAdult::OTHER,
            'back_url' => urldecode($param['back_url'] ?? ''),
            'type' => $param['type'] ?? '',
            'sign' => strtolower($param['sign'] ?? ''),
        );

        return $login_data;
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
        $aData = static::uniformPayData(static::paramData());
        $uData = array_filter(
            $aData,
            function ($v) {
                return !is_null($v);
            }
        );
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
        $aData = static::uniformLoginData(static::paramData());
        $uData = array_filter(
            $aData,
            function ($v) {
                return !is_null($v);
            }
        );
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
        $payObj = static::parse($data);
        try {
            $mod = new static;
            $mod->_eventBefore($payObj);
            $res = User::saveUser($payObj, 'pay');
            $result = Payment::storeData($payObj);
            array_push($result, $res);

            return $result;
        } catch (\Exception $e) {
            self::errLog($e, 'pay_error');

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
        $loginObj = static::parse($data);

        if (isset($loginObj->time) && $loginObj->time > 0 && isset($loginObj->uid) && $loginObj->uid) {
            try {
                LoginLogTable::$month = date('Ym', $loginObj->time);
                $mod = new static();
                $mod->_eventBefore($loginObj);
                LoginLogTable::newTable($loginObj->time);
                $result = LoginLogTable::storeData($loginObj);
                if ($result) {
                    static::$id = $result;
                    $mod->_eventAfter();

                    return [date('Y-m-d H:i:s',$loginObj->time).' new', $result, static::$user_id];
                }

                return [date('Y-m-d H:i:s',$loginObj->time).' old', '', ''];

            } catch (\Exception $e) {
                self::errLog($e, 'login_error');

                return ['', '', ''];
            }
        }

        return ['', '', ''];
    }

    protected static function errLog(\Exception $e, $logFilename)
    {
        $error = $e->getMessage().$e->getFile().$e->getLine().PHP_EOL;
        var_dump($error);
        $dir = \Yii::getAlias('@console').'/runtime/errors/';
        if (!is_dir($dir)) {
            mkdir($dir);
            chmod($dir, 0777);
        }
        $file = $dir.$logFilename.'.log';
        file_put_contents($file, date('YmdHis').$error.$e->getTraceAsString().PHP_EOL, FILE_APPEND);
    }

    protected function _eventBefore($data)
    {
//        $this->on(static::EVENT_BEFORE_CREATE, [$this, '_eventAddLoginTable'], $data);
        $this->on(static::EVENT_BEFORE_CREATE, [$this, '_eventAddPlatform'], $data);
        $this->on(static::EVENT_BEFORE_CREATE, [$this, '_eventAddServer'], $data);
        $this->trigger(static::EVENT_BEFORE_CREATE);
    }

    protected function _eventAfter()
    {
        $this->on(static::EVENT_AFTER_CREATE, [$this, '_eventSaveUser']);
        $this->trigger(static::EVENT_AFTER_CREATE);
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
        $obj = static::createObj();

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
        $url_param = strpos(static::$url_param, '=') ? static::$url_param : urldecode(static::$url_param);
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
                $newParam[trim($p[0], ' ')] = $p[1];
            }
        }

        return $newParam;
    }
}
