<?php

namespace console\controllers;

use console\models\LogTable;
use console\models\platform\Platform37;
use console\models\platform\Platform4399;
use console\models\platform\PlatformDefault;
use console\models\platform\PlatformPPS;
use console\models\platform\PlatformSoGou;
use console\models\platform\PlatformXunLei;
use console\models\platform\PlatformYY;
use yii\console\Controller;

class PayController extends Controller
{
    const PAY_4399 = '/4399/pay.php';
    const PAY_XUN_LEI = '/xunlei/pay.php';
    const PAY_YY = '/yy/pay.php';
    const PAY_PPS = '/pps/pay.php';
    const PAY_37 = '/37/pay.php';
    const PAY_SO_GOU = '/sogou/pay.php';
    const PAY = '/api/pay.php';
    //per hour run
    public function actionRun($from = null, $to = null)
    {
        if ($from == null || $to == null) {
            $from = date('Y-m-d H:i', strtotime('-5 minute'));
            $to = date('Y-m-d H:i', strtotime('now'));
        } else {
            $from = date('Y-m-d H:i', strtotime($from));
            $to = date('Y-m-d H:i', strtotime($to));
        }

        //记录日志
        $this->logPay($from, $to);
    }

    public function logPay($from, $to)
    {
        $diff = LogTable::getDiffDay($from, $to);
        //搜索大于1天
        if (!empty($diff)){
            foreach ($diff as $k => $v) {
                $f = current($diff);
                $t = next($diff);
                if ($f && $t){
                    $this->SlaveUrl(date('Ym', strtotime($f)), $f, $t);
                }
            }
        } else {
            $monthArr = LogTable::logTableMonth($from, $to);
            foreach ($monthArr as $month) {
                $this->SlaveUrl($month, $from, $to);
            }
        }
    }

    protected function SlaveUrl($month = null, $from = null, $to = null)
    {
        LogTable::$month = $month ?: date('Ym');
        $data = LogTable::find()
            ->select('url, post_data')
            ->andFilterWhere(['>=', 'stamp', strtotime($from)])
            ->andFilterWhere(['<', 'stamp', strtotime($to)]);

        foreach ($data->each(100) as $v) {
            if (!stripos($v->url, 'pay')) {
                continue;
            }
            $this->stdout($v->url.PHP_EOL);
            if (!strpos($v->url, '?')) {
                if (!stripos($v->url, 'pay') || !($v->post_data && strlen($v->post_data) > 10 && stristr(
                            $v->post_data,
                            '='
                        ) && stristr($v->post_data, '&'))) {
                    continue;
                }
                PlatformSoGou::$url_param = $v->post_data;
                $result = PlatformSoGou::savePay();
                $this->stdout(
                    $result[0].' payment ID: '.$result[1].' info: '.$result[2].(isset($result[3]) && $result[3] ? ' new_uid:'.$result[3] : '').PHP_EOL
                );
            }
            $urlArr = explode('?', $v->url);
            if(!stripos($urlArr[0], 'pay')) {
                continue;
            }
            foreach ($this->map() as $k => $class)
            {
                if (stristr($urlArr[0], $k)){
                    $class::$url_param = $urlArr[1];
                    $result = $class::savePay();
                    $this->stdout(
                        $result[0].' payment ID: '.$result[1].' info: '.$result[2].(isset($result[3]) && $result[3] ? ' new_uid:'.$result[3] : '').PHP_EOL
                    );
                }
            }
        }
    }

    protected function map()
    {
        return [
            self::PAY => PlatformDefault::class,
            self::PAY_PPS => PlatformPPS::class,
            self::PAY_YY => PlatformYY::class,
            self::PAY_XUN_LEI => PlatformXunLei::class,
            self::PAY_37 => Platform37::class,
//            self::PAY_SO_GOU => PlatformSoGou::class,
            self::PAY_4399 => Platform4399::class,
        ];
    }
}
