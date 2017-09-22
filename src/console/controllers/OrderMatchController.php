<?php
namespace console\controllers;

use yii\console\Controller;
use common\models\OrderMatch;
class OrderMatchController extends Controller
{
    //订单比对
    public function actionRun($from = null, $to = null)
    {
        if ($from == null || $to == null) {
            $from = date('Y-m-01', strtotime('-1 month'));
            $to = date('Y-m-01', strtotime('now'));
        }
        $filename = \Yii::getAlias('@backend').'/file/9377_0913.csv';//
        $batch = mt_rand(100,999);
//        OrderMatch::getRepeat($filename, 1, 'r.csv');
//        OrderMatch::weH($filename, 14,$from, $to, $batch);
//        OrderMatch::orderOut(1001, 14, $from, $to, 'yy7.csv');
        OrderMatch::fileMatchDB($filename, 1, 1001,3, $from, $to, $batch);
    }
}