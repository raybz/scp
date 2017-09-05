<?php

namespace console\controllers;

use common\models\Game;
use common\models\online\TLZJGameOnline;
use common\models\OnlineData;
use yii\console\Controller;

class OnlineController extends Controller
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

        $this->perDayDo($from, $to);
    }

    protected function perDayDo($from, $to)
    {
        $diff = ceil((strtotime($to) - strtotime($from)) / 86400);

        for($i = 0; $i < $diff; $i++) {
            $f = date('Y-m-d', strtotime($from.($i).' day'));
            $t = date('Y-m-d', strtotime($from.($i + 1).' day'));
            $this->down($f, $t);
        }
    }

    protected function down($from, $to)
    {
        //后期修改为多游戏
        try {
            $game = new TLZJGameOnline();
            $game->from = strtotime($from);
            $game->to = strtotime($to);
            $result = $game->getOnLine();
            $data = $result;
            $data['date'] = $from;
            $game = Game::getGameByGKey($result['gKey']);
            $data['game_id'] = $game->id;

            $out = OnlineData::storeData($data);
            $this->stdout('date: '.$from.' '.$out[0].' ID: '.$out[1].PHP_EOL);
        } catch (\Exception $e) {
            var_dump($e->getTraceAsString());
        }
    }
}