<?php

namespace console\controllers;

use common\models\DayArrange;
use common\models\Game;
use common\models\Payment;
use common\models\User;
use console\models\LoginLogTable;
use yii\console\Controller;

class DayArrangeController extends Controller
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
        $this->collect($from, $to);
    }

    protected function collect($from, $to)
    {
        $day = intval((strtotime($to) - strtotime($from)) / 86400);
        for ($i = 0; $i < $day; $i++) {
            $f = date('Y-m-d', strtotime($from.$i.' day'));
            $t = date('Y-m-d', strtotime($from.($i + 1).' day'));
            $game_list = Game::gameList();
            $data = [];
            foreach ($game_list as $game) {
                $newUser = [];
                $user = User::newRegister($f, $t, $game['id']);
                foreach ($user as $u){
                    $newUser[] = $u->id;
                }
//                var_dump(Payment::getPerTimeMan($game['id'], $f, $t, $newUser));exit;
                $user_new_total = count($user);
                $pay_money_sum = Payment::getPerTimeMoney($game['id'], $f, $t);
                $pay_man_sum = Payment::getPerTimeMan($game['id'], $f, $t);
                $data['gid'] = $game['id'];
                $data['date'] = $f;
                $data['register'] = $user_new_total;
                $data['active'] = $this->getActive($game['id'], $f, $t);
                $data['max_online'] = $this->getMaxOnline();
                $data['avg_online'] = $this->getAvgOnline();
                $data['pay_money_sum'] = $pay_money_sum;
                $data['pay_man_sum'] = $pay_man_sum;

                $data['register_pay_man_sum'] = Payment::getPerTimeMan($game['id'], $f, $t, $newUser);
                $data['register_pay_money_sum'] = Payment::getPerTimeMoney($game['id'], $f, $t, $newUser);
                $result = DayArrange::storeData($data);
                $this->stdout('ID:'.$result.PHP_EOL);
            }
        }
    }

    protected function getMaxOnline()
    {
        //todo api 请求
        return 0;
    }

    protected function getAvgOnline()
    {
        return 0;
    }

    protected function getActive($gid, $f, $t)
    {
        LoginLogTable::$month = date('Ym', strtotime($f));
        $login = LoginLogTable::find()
            ->where(['>=', 'time', $f])
            ->andWhere(['<', 'time', $t])
            ->andWhere('gid = :g', [':g' => $gid])
            ->groupBy('uid,platform');
        $active = [];
        if($login){
            foreach ($login->each(100) as $l){
                $user = User::find()
                    ->where('created_at' < $f)
                    ->andWhere('uid = :uid', [':uid' => $l->uid])
                    ->andWhere('platform = :p', [':p' => $l->platform])
                    ->andWhere('gid = :g', [':g' => $l->gid]);
                if($user){
                    $active[] = $user;
                }
            }
        }
        return count($active);
    }
}
