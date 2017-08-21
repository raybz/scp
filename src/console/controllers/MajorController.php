<?php

namespace console\controllers;

use common\models\Major;
use common\models\Payment;
use common\models\Platform;
use common\models\User;
use console\models\LoginLogTable;
use yii\console\Controller;

class MajorController extends Controller
{
    //per day run
    public function actionRun($from = null, $to = null)
    {
        if ($from == null || $to == null) {
            $from = date('Y-m-d', strtotime('yesterday'));
            $to = date('Y-m-d', strtotime('now'));
        } else {
            $from = date('Y-m-d', strtotime($from));
            $to = date('Y-m-d', strtotime($to.'+1 day'));
        }

        //记录日志
        $this->payDo($from, $to);
    }

    protected function payDo($from, $to)
    {
        $monthArr = LoginLogTable::logTableMonth($from, $to);
        foreach ($monthArr as $month) {
            $this->getUserPay($month, $from, $to);
        }
    }

    protected function getUserPay($month, $from, $to)
    {


        $pay = Payment::find()
            ->where(['>=', 'time', $from])
            ->andWhere(['<', 'time', $to]);
        foreach ($pay->each() as $p) {
            $user = User::findOne($p->user_id);
            if (!$user) {
                continue;
            }
            $major = Major::getMajor($user->id, $p->game_id);
            //is major
            if ($major) {
                $platform = Platform::findOne($p->platform_id);
                $this->saveMajor($month, $major, $platform->id);
            }
            //to major

        }

    }

    protected function saveMajor($month, Major $major, $platform_id, $uid, $from, $to)
    {
        LoginLogTable::$month = $month ?: date('Ym');

        $count = LoginLogTable::getUserLoginCount($uid, $platform_id, $from, $to);
        $latest_login_at = LoginLogTable::getUserLatestLogin($uid, $platform_id);

        $major->login_count = $count;
        $major->payment_count += 1;
        $major->total_payment_amount += isset($p->money) ? ($p->money) * 100 : 0;
        $major->latest_login_at = strtotime($p->time) > strtotime(
            $latest_login_at
        ) ? $p->time : $latest_login_at;

        if ($major->save()) {
            return $major->id;
        }
    }
}