<?php

namespace backend\controllers;

use common\models\Game;
use common\models\Payment;
use Yii;
use yii\helpers\Json;
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
        $man = boolval(\Yii::$app->request->get('type')) == true ?: false;
        $dataAll = [];
        $total = 0;
        for ($i = 0; $i < 24; $i++) {
            $from = date('Y-m-d H:i:s', strtotime(date('Y-m-d').$i.' hour'));
            $to = date('Y-m-d H:i:s', strtotime(date('Y-m-d').($i + 1).' hour'));
            if (!$man) {
                $dataAll[] = intval(Payment::getPerTimeMoney('', $from, $to));
            } else {
                $dataAll[] = intval(Payment::getPerTimeMan('', $from, $to));
            }
            $total += intval($dataAll[$i]);
        }

        $rangeTime = range(0, 23);
        $rangeData = [];
        $data = [];
        $series = [];
        if (!empty($dataAll)) {
            foreach ($rangeTime as $hour) {
                $k = str_pad($hour, 2, 0, STR_PAD_LEFT);
                $rangeData[] = $k.':00';
            }
            $data = $dataAll;
        }
        if ($data) {
            $series = [
                'name' => '累计',
                'colorByPoint' => true,
                'data' => $data,
            ];
        }

        return [
            'code' => 200,
            'data' => [
                'title' => $total,
                'xAxis' => $rangeData,
                'series' => [$series],
            ],
        ];
    }

    public function actionGamePaymentSpline()
    {
        $from = Yii::$app->request->get('from', date('Y-m-d'));
        $to = Yii::$app->request->get('to', date('Y-m-d', strtotime('tomorrow')));
        $platform = Yii::$app->request->get('platform');
        $gameId = Yii::$app->request->get('gid');
        $man = boolval(\Yii::$app->request->get('type')) == true ?: false;
        $dataAll = [];
        $platformList = Json::decode($platform);
        $gameList = Json::decode($gameId);
        $diff_day = intval((strtotime($to) - strtotime($from))/86400);
        $rangeTime = range(0, $diff_day - 1);
        $rangeData = [];
        foreach ($rangeTime as $day) {
            $rangeData[] = date('Y-m-d', strtotime($from. $day.' day'));
            foreach ($gameList as $game) {
                if (is_numeric($game)) {
                    $g = Game::findOne($game);
                    $dataAll[$game]['name'] = $g['name'];
                    $f = date('Y-m-d', strtotime($from.$day.' day'));
                    $t = date('Y-m-d', strtotime($from.($day + 1).' day'));
                    if (!$man) {
                        $dataAll[$game]['data'][] = intval(Payment::getPerTimeMoney($game, $f, $t, '', $platformList));
                    } else {
                        $dataAll[$game]['data'][] = intval(Payment::getPerTimeMan($game, $f, $t, '', $platformList));
                    }
                }
            }
        }
            $data = array_values($dataAll);

        return [
            'code' => 200,
            'data' => [
                'title' => '',
                'xAxis' => $rangeData,
                'series' => $data,
            ],
        ];
    }

    public function actionGamePaymentPie()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $gameId = Yii::$app->request->get('gid');
        $platform = Yii::$app->request->get('platform');

        $from = Yii::$app->request->get('from', date('Y-m-d'));
        $to = Yii::$app->request->get('to', date('Y-m-d'));
        $data = [];
        $series = [];
        $gameList = Json::decode($gameId);
        $platformList = Json::decode($platform);
        $totalMoney = Payment::getPerTimeMoney($gameList, $from, $to, '', $platformList);

        foreach ($gameList as $game) {
            if (is_numeric($game)) {
                if ($totalMoney <= 0) {
                    $y = 0;
                } else {
                    $perGameSum = Payment::getPerTimeMoney($game, $from, $to);
                    $y = round(intval($perGameSum) / $totalMoney, 4) * 100;
                }
                $g = Game::findOne($game);
                $data[] = [
                    'name' => $g ? $g['name'] : '',
                    'y' => $y,
                ];
            }
        }
        if ($data) {
            $series = [
                'name' => '占比',
                'colorByPoint' => true,
                'data' => $data,
            ];
        }

        return [
            'code' => 200,
            'data' => [
                'series' => [$series],
            ],
        ];
    }
}