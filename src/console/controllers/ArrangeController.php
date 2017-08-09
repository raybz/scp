<?php

namespace console\controllers;

use common\models\Arrange;
use common\models\DayArrange;
use common\models\Game;
use common\models\Payment;
use common\models\User;
use console\models\LoginLogTable;
use yii\console\Controller;
use yii\db\Query;

class ArrangeController extends Controller
{
    //per day run
    public function actionRun($from = null, $to = null)
    {
        if ($from == null || $to == null) {
            $from = date('Y-m-d', strtotime('yesterday'));
            $to = date('Y-m-d', strtotime('today'));
        } else {
            $from = date('Y-m-d', strtotime($from));
            $to = date('Y-m-d', strtotime($to));
        }
        $this->rePayment($from, $to);
    }

    protected function getActive($data, $f, $t)
    {
        LoginLogTable::$month = date('Ym', strtotime($f));
        $login = LoginLogTable::find()
            ->where(['>=', 'time', $f])
            ->andWhere(['<', 'time', $t])
            ->andWhere('gid = :g', [':g' => $data['gid']])
            ->andWhere('platform = :p', [':p' => $data['platform']])
            ->andWhere('server_id = :s', [':s' => $data['server_id']])
            ->groupBy('uid,platform,server_id');
        $active = [];
        if($login){
            foreach ($login->each(100) as $l){
                $user = User::find()
                    ->where('created_at' < $f)
                    ->andWhere('uid = :uid', [':uid' => $l->uid])
                    ->andWhere('platform = :p', [':p' => $l->platform])
                    ->andWhere('server_id = :s', [':p' => $l->server_id])
                    ->andWhere('gid = :g', [':g' => $l->gid]);
                if($user){
                    $active[] = $user;
                }
            }
        }
        return count($active);
    }

    protected function rePayment($from, $to)
    {
        $day = intval((strtotime($to) - strtotime($from)) / 86400);

        for ($i = 0; $i < $day; $i++) {
            $f = date('Y-m-d', strtotime($from.$i.' day'));
            $t = date('Y-m-d', strtotime($from.($i + 1).' day'));

            $ps = (new Query())->from('payment')
                ->select('*, sum(money) as tMoney, count(*) as cp')
                ->andFilterWhere(['>=', 'time', $f])
                ->andFilterWhere(['<', 'time', $t])
                ->groupBy('gid,platform_id,server_id');

            foreach ($ps->each(100) as $p) {
                $this->store($f, $t, $p);
            }
        }
    }

    protected function store($f, $t, $d)
    {
        $newUser = [];
        $user = User::newRegister($f, $t, $d['gid'], $d['platform_id'], $d['server_id']);
        foreach ($user as $u){
            $newUser[] = $u->id;
        }
        $user_new_total = count($user);
        $pay_money_sum = $d['tMoney'];
        $pay_man_sum = $d['cp'];
        $data['game_id'] = $d['gid'];
        $data['date'] = $f;
        $data['platform_id'] = $d['platform_id'];
        $data['server_id'] = $d['server_id'];
        $data['new'] = $user_new_total;
        $data['active'] = $this->getActive($d, $f, $t);
        $data['pay_money'] = $pay_money_sum;
        $data['pay_man'] = $pay_man_sum;
        $data['new_pay_man'] = Payment::getPerTimeMan($d['gid'], $f, $t, $newUser, $d['platform_id'], $d['server_id']);
        $data['new_pay_money'] = Payment::getPerTimeMoney($d['gid'], $f, $t, $newUser, $d['platform_id'], $d['server_id']);
        $result = Arrange::storeData($data);
        $this->stdout('ID:'.$result.PHP_EOL);
    }
}
