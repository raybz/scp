<?php
namespace backend\controllers;

use common\models\Game;
use common\models\Payment;
use yii\web\Controller;
use yii\web\Response;

class ApiController extends Controller
{
    public function init()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        parent::init();
    }

    public function actionTodayPaymentSpline()
    {
        $man =  boolval(\Yii::$app->request->get('type')) == true ?: false;
        $dataAll = [];
        $gameList = Game::gameList();
        $total = 0;
        foreach ($gameList as $game) {
            $dataAll[$game['id']]['name'] = $game['name'];
            for($i = 0; $i< 24 ;$i++) {
                $from = strtotime(date('Y-m-d').$i.' hour');
                $to = strtotime(date('Y-m-d').($i+1).' hour');
                if (!$man) {
                    $dataAll[$game['id']]['data'][] = intval(Payment::getPerTimeMoney($from, $to, $game['id']));
                } else {
                    $dataAll[$game['id']]['data'][] = intval(Payment::getPerTimeMan($from, $to, $game['id']));
                }
                $total += intval($dataAll[$game['id']]['data'][$i]);
            }
        }

        $rangeTime = range(0, 23);
        $rangeData = [];
        $data = [];
        if (!empty($dataAll)) {
            foreach ($rangeTime as $hour) {
                $k = str_pad($hour, 2, 0, STR_PAD_LEFT);
                $rangeData[] = $k.':00';
            }
            $data = array_values($dataAll);
        }

        return [
            'code' => 200,
            'data' => [
                'title' => $total,
                'xAxis' => $rangeData,
                'series' => $data,
            ],
        ];
    }
}