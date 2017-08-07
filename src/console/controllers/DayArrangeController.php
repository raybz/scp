<?php

namespace console\controllers;

use common\models\DayArrange;
use common\models\Game;
use common\models\Payment;
use common\models\User;
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
                $user = User::newRegister($f, $t, $game['id']);
                $user_new_total = count($user);
                $pay_money_sum = Payment::getPerTimeMoney(strtotime($f), strtotime($t), $game['id']);
                $pay_man_sum = Payment::getPerTimeMan(strtotime($f), strtotime($t), $game['id']);
                $data['gid'] = $game['id'];
                $data['date'] = $f;
                $data['register'] = $user_new_total;
                $data['max_online'] = $this->getMaxOnline();
                $data['avg_online'] = $this->getAvgOnline();
                $data['pay_money_sum'] = $pay_money_sum;
                $data['pay_man_sum'] = $pay_man_sum;
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
}
