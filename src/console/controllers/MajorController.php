<?php

namespace console\controllers;

use common\definitions\MajorType;
use common\models\Major;
use common\models\MajorLoginHistory;
use common\models\Payment;
use common\models\Platform;
use common\models\User;
use console\models\LoginLogTable;
use yii\console\Controller;

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
        $this->perDayDo($from, $to);
    }

    protected function perDayDo($from, $to)
    {
        $diff = ceil((strtotime($to) - strtotime($from)) / 86400);

        for($i = 0; $i < $diff; $i++) {
            $f = date('Y-m-d', strtotime($from.($i).' day'));
            $t = date('Y-m-d', strtotime($from.($i + 1).' day'));
            $this->payDo($f, $t);
        }

    }

    protected function payDo($from, $to)
    {
        try {
            $this->getUserPay($from, $to);
            $monthArr = LoginLogTable::logTableMonth($from, $to);
            foreach ($monthArr as $month) {
                $this->majorLogin($month, $from, $to);
            }
        } catch (\Exception $e) {
            $this->stderr($e->getTraceAsString().$e->getLine().$e->getMessage().PHP_EOL);exit;
        }

    }

    protected function getUserPay($from, $to)
    {
        //查询新订单
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
                $res = Major::saveMajorPay($major, $p);
                $this->stdout('old major ID: '.$res.' time: '.$from.PHP_EOL);
            } //to major
            else {
                $res = Major::newRunMajor($p, $user);
                if ($res) {
                    $this->stdout('new major ID: '.$res.' time: '.$from.PHP_EOL);
                }
            }
        }
    }

    protected function majorLogin($month, $from, $to)
    {
        $this->stdout('from: '.$from.' - to: '.$to.PHP_EOL);
        $majorAll = Major::find();
        foreach ($majorAll->each() as $major) {
            if ($major->created_at > $from) {
                continue;
            }
            $user = User::findOne($major->user_id);
            LoginLogTable::$month = $month ?: date('Ym');
            $platform = Platform::findOne($major->platform_id);
            $count = LoginLogTable::getUserLoginCount($user->uid, $platform->abbreviation, $from, $to);
            $latest_login_at = (LoginLogTable::getUserLatestLogin(
                    $user->uid,
                    $platform->abbreviation,
                    $from,
                    $to
                )->time) ?? '';

            $pay = Payment::getMajorPay($from, $to, $major->user_id);

            $data['date'] = date('Y-m-d', strtotime($from));
            $data['money'] = $pay['pMoney'] ?? 0;
            $data['pay_times'] = $pay['pay_times'] ?? 0;

            $data['major_id'] = $major->id;
            $data['latest_login_at'] = $latest_login_at ?: '';
            $data['login_count'] = $count;
            //
            $f = date('Y-m-d', strtotime($from.' -3 day'));
            $had = MajorLoginHistory::getMajorHistoryExist($major->id, $to);
            $exist = MajorLoginHistory::getMajorHistoryExist($major->id, $to, $f);

            Major::upType($major->id, $had, $exist, $latest_login_at);
            $this->stdout('update Major: '.$major->id.PHP_EOL);
            if (!$latest_login_at) {
                continue;
            }

            $newLogin = MajorLoginHistory::storeData($data);
            $this->stdout('major ID: '.$major->id.' new login: '.$newLogin.PHP_EOL);
        }
    }

}