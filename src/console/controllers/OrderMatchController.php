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
        $filename = \Yii::getAlias('@backend').'/file/yy.csv';
        $batch = mt_rand(100,999);
//        OrderMatch::fileContext($filename, 14,$from, $to);
        OrderMatch::weH($filename, 14,$from, $to, $batch);
    }
}