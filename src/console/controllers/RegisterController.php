<?php

namespace console\controllers;

use common\models\User;
use console\models\LoginLogTable;
use yii\console\Controller;

class RegisterController extends Controller
{
    //todo drop
    //per day run
    public function actionRun($from = null, $to = null)
    {
        if ($from == null || $to == null) {
            $from = date('Y-m-d', strtotime('yesterday'));
            $to = date('Y-m-d 23:59:59', strtotime('yesterday'));
        } else {
            $from = date('Y-m-d', strtotime($from));
            $to = date('Y-m-d 23:59:59', strtotime($to));
        }
        //记录日志
        $this->newUser($from, $to);
    }

    public function newUser($from, $to)
    {
        $monthArr = LoginLogTable::logTableMonth($from, $to);
        foreach ($monthArr as $month) {
            $this->storeUser($month, $from, $to);
        }
    }

    protected function storeUser($month = null, $from = null, $to = null)
    {
        LoginLogTable::$month = $month ?: date('Ym');
        $login = LoginLogTable::find()
            ->andFilterWhere(['>=', 'time', strtotime($from)])
            ->andFilterWhere(['<=', 'time', strtotime($to)]);
        foreach ($login->each(100) as $l){
            $user = User::getUser($l->uid, $l->platform, $l->gid);
            if ($user) {
                $this->stdout('old User ID: '.$user->id.PHP_EOL);
                continue;
            }
            /* @var $user LoginLogTable*/
            $result = User::newUser($l);
            $this->stdout('new User ID: '.$result.PHP_EOL);
        }
    }
}
