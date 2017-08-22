<?php

namespace console\controllers;

use common\models\Major;
use common\models\Payment;
use common\models\Platform;
use common\models\User;
use console\models\LoginLogTable;
use yii\console\Controller;
use yii\helpers\Json;

class MajorController extends Controller
{
    const THRESHOLD = 3000;

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
                $res = $this->saveMajor($month, $major, $p, $user->uid, $to);
                $this->stdout('old major ID:'.$res.PHP_EOL);
            } //to major
            else {
                $res = $this->newMajor($month, $p, $user, $to);
                if ($res) {
                    $this->stdout('new major ID: '.$res.PHP_EOL);
                }
            }
        }

    }

    protected function saveMajor($month, Major $major, Payment $payment, $uid, $to, $money = null)
    {
        LoginLogTable::$month = $month ?: date('Ym');
        $platform = Platform::findOne($payment->platform_id);
        $count = LoginLogTable::getUserLoginCount($uid, $platform->abbreviation, '', $to);
        $latest_login_at = (LoginLogTable::getUserLatestLogin($uid, $platform->abbreviation)->time) ?? 0;

        $major->login_count = $count;
        $major->payment_count = Payment::getPerTimeMan(
            $payment->game_id,
            '',
            $to,
            $payment->user_id,
            $payment->platform_id
        );
        $major->total_payment_amount = $money ? $money * 100 : Payment::getPerTimeMoney(
                $payment->game_id,
                '',
                $to,
                $payment->user_id,
                $payment->platform_id
            ) * 100;
        $major->latest_login_at = strtotime($payment->time) > strtotime(
            $latest_login_at
        ) ? $payment->time : $latest_login_at;

        if ($major->save()) {
            return $major->id;
        } else {
            return Json::encode($major->errors);
        }
    }

    protected function newMajor($month, Payment $p, User $user, $to)
    {
        $money = Payment::getPerTimeMoney($p->game_id, '', $to, $p->user_id, $p->platform_id);
        //大于等于3000
        if ($money >= self::THRESHOLD) {
            $major = new Major();
            $major->user_id = $p->user_id;
            $major->game_id = $p->game_id;
            $major->platform_id = $p->platform_id;
            $major->is_adult = $user->is_adult;
            $major->register_at = $user->register_at;
            $major->created_at = date('Y-m-d H:i:s');

            return $this->saveMajor($month, $major, $p, $user->uid, $to, $money);
        }

        return null;
    }
}