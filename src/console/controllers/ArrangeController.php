<?php

namespace console\controllers;

use common\models\Arrange;
use common\models\DayArrange;
use common\models\Payment;
use common\models\User;
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

    protected function rePayment($from, $to)
    {
        $day = intval((strtotime($to) - strtotime($from)) / 86400);
        //订单按天计算
        for ($i = 0; $i < $day; $i++) {
            $f = date('Y-m-d', strtotime($from.$i.' day'));
            $t = date('Y-m-d', strtotime($from.($i + 1).' day'));

            $ps = (new Query())->from('payment')
                ->select([
                    '*',
                    'sum(money) as tMoney',//区服营收
                    'count(*) as cp',//区服登录用户
                    'COUNT(DISTINCT user_id) um'// 区服去重用户
                ])
                ->andFilterWhere(['>=', 'time', $f])
                ->andFilterWhere(['<', 'time', $t])
                ->groupBy('game_id,platform_id,server_id');
            //单日区服记录保存
            foreach ($ps->each(100) as $p) {
                $this->store($f, $t, $p);
            }
        }
    }

    protected function store($f, $t, $d)
    {
        $newUser = [];
        $user = User::newRegister($f, $t, $d['game_id'], $d['platform_id'], $d['server_id']);
        foreach ($user as $u) {
            $newUser[] = $u->id;
        }
        $user_new_total = count($user);
        $data['game_id'] = $d['game_id'];
        $data['date'] = $f;
        $data['platform_id'] = $d['platform_id'];
        $data['server_id'] = $d['server_id'];
        $data['new'] = $user_new_total;
        $data['active'] = Arrange::getActive($d, $f, $t);//活跃为当日非注册用户,历史老用户在此区服今日登录
        $data['pay_money'] = $d['tMoney'];
        $data['pay_man'] = $d['um'];
        $data['pay_man_time'] = $d['cp'];
        $data['new_pay_man'] = empty($newUser) ? 0 : Payment::getPerTimeMan(
            $d['game_id'],
            $f,
            $t,
            $newUser,
            $d['platform_id'],
            $d['server_id']
        );
        $data['new_pay_money'] = empty($newUser) ? 0 : Payment::getPerTimeMoney(
            $d['game_id'],
            $f,
            $t,
            $newUser,
            $d['platform_id'],
            $d['server_id']
        );
        $result = Arrange::storeData($data);
        $this->stdout($result[0].' ID: '.$result[1].' date:'.$f.PHP_EOL);
    }
}
