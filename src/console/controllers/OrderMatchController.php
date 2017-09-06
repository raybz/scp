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
        $filename = \Yii::getAlias('@backend').'/file/yy8.csv';//
        $batch = mt_rand(100,999);
//        OrderMatch::fileContext($filename, 3,$from, $to, $filename2);
//        OrderMatch::weH($filename, 14,$from, $to, $batch);
//        OrderMatch::orderOut(1001, 14, $from, $to, 'yy7.csv');
        OrderMatch::fileMatchDB($filename, 0, 14, $from, $to, $batch);
    }
}