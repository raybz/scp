<?php

namespace backend\controllers;

use common\models\Arrange;
use common\models\Game;
use common\models\Payment;
use common\models\Platform;
use Yii;
use yii\db\Query;
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
        $platformList = unserialize($platform);
        $gameList = unserialize($gameId);
        $diff_day = intval((strtotime($to) - strtotime($from)) / 86400);
        $rangeTime = range(0, $diff_day - 1);
        $rangeData = [];
        foreach ($rangeTime as $day) {
            $rangeData[] = date('Y-m-d', strtotime($from.$day.' day'));
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
        $gameId = Yii::$app->request->get('gid');
        $platform = Yii::$app->request->get('platform');

        $from = Yii::$app->request->get('from', date('Y-m-d'));
        $to = Yii::$app->request->get('to', date('Y-m-d'));
        $data = [];
        $series = [];
        $gameList = unserialize($gameId);
        $platformList = unserialize($platform);
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

    public function actionPlatformPaymentPie()
    {
        $gameId = Yii::$app->request->get('gid');
        $platform = Yii::$app->request->get('platform');

        $from = Yii::$app->request->get('from', date('Y-m-d'));
        $to = Yii::$app->request->get('to', date('Y-m-d'));
        $gameList = unserialize($gameId);
        $platformList = unserialize($platform);
        $pTotal = Arrange::getDataByPlatform($from, $to, $gameList, $platformList);
        $tMoney = [];
        foreach ($pTotal as $p) {
            $tMoney[] = $p['pay_money_sum'];
        }
        $totalMoney = array_sum($tMoney);
        $data = [];
        $series = [];


        foreach ($platformList as $platform) {
            if (is_numeric($platform)) {
                if ($totalMoney <= 0) {
                    $y = 0;
                } else {
                    $perPlatformSum = $pTotal[$platform]['pay_money_sum'] ?? 0;
                    $y = round(($perPlatformSum) / $totalMoney * 100, 2);
                }
                $g = Platform::findOne($platform);
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

    public function actionPlatformPaymentSpline()
    {
        $from = Yii::$app->request->get('from', date('Y-m-d'));
        $to = Yii::$app->request->get('to', date('Y-m-d', strtotime('tomorrow')));
        $platform = Yii::$app->request->get('platform', null);
        $gameId = Yii::$app->request->get('gid', null);

        $platformList = unserialize($platform);
        $gameList = unserialize($gameId);

        $diff_day = intval((strtotime($to) - strtotime($from)) / 86400);

        $rangeTime = range(0, $diff_day - 1);
        $dataAll = $data = $rangeData = [];
        if ($platformList) {
            foreach ($rangeTime as $day) {
                $rangeData[] = date('Y-m-d', strtotime($from.$day.' day'));
                foreach ($platformList as $platform) {
                    if (is_numeric($platform)) {

                        $g = Platform::findOne($platform);

                        $dataAll[$platform]['name'] = $g['name'];

                        $f = date('Y-m-d', strtotime($from.$day.' day'));
                        $t = date('Y-m-d', strtotime($from.($day + 1).' day'));
                        $pTotal = Arrange::getDataByPlatform($f, $t, $gameList, $platformList);
                        $dataAll[$platform]['data'][] = isset($pTotal[$platform]['pay_money_sum']) ? intval(
                            $pTotal[$platform]['pay_money_sum']
                        ) : 0;
                    }
                }
            }
            $data = array_values($dataAll);
        }

        return [
            'code' => 200,
            'data' => [
                'title' => '',
                'xAxis' => $rangeData,
                'series' => $data,
            ],
        ];
    }

    public function actionServerPaymentBar()
    {
        $from = Yii::$app->request->get('from', date('Y-m-d'));
        $to = Yii::$app->request->get('to', date('Y-m-d', strtotime('tomorrow')));
        $platform = Yii::$app->request->get('platform');
        $gameId = Yii::$app->request->get('gid');
        $server = Yii::$app->request->get('server');

        $platformList = unserialize($platform);
        $serverList = unserialize($server);

        $rangeData = [];

        $pl = (new Query())->from('arrange')
            ->select([
                'date',
                'game_id',
                'platform_id',
                'server_id',
                'sum(new) new_sum',
                'sum(active) active_sum',
                'sum(pay_man) pay_man_sum',
                'sum(pay_money) pay_money_sum',
                'sum(new_pay_man) new_pay_man_sum',
                'sum(new_pay_money) new_pay_money_sum',
            ])
            ->where('date >= :from AND date < :to',
                [
                    ':from' => $from,
                    ':to' => $to
                ])
            ->andFilterWhere(['game_id' => $gameId])
            ->andFilterWhere(['platform_id' => $platformList])
            ->andFilterWhere(['server_id' => $serverList])
            ->groupBy('platform_id,server_id')
            ->orderBy('pay_money_sum DESC')
            ->limit(10)
            ->all();

        $res = Arrange::getDataByPlatform($from, $to, $gameId, $platformList);
        $pay_sum_total = [];
        $new_sum_total = [];
        foreach($res as $re){
            $pay_sum_total[] = $re['pay_money_sum'];
            $new_sum_total[] = $re['new_sum'];
        }

        $bar = [
            '总充值金额（百分比）',
            '新增用户数（百分比）',
            '活跃用户数',
        ];
        $data = $data1 = $data2 = $data3 =  [];
        foreach ($pl as $r) {
            $pf = Platform::findOne($r['platform_id']);
            $rangeData[] = isset($pf->name) ? $pf->name.' / '.$r['server_id'].'区' : '';
            $data1[] = round($r['pay_money_sum'] / array_sum($pay_sum_total) * 100, 2) ?:0;
            $data2[] = round($r['new_sum'] / array_sum($new_sum_total) * 100, 2) ?: 0;
            $data3[] = intval($r['active_sum']) ?: 0;
        }
        foreach ($bar as $k => $b) {
            $data[$k]['name'] = $b;
        }
        $data[0]['data'] = $data1;
        $data[1]['data'] = $data2;
        $data[2]['data'] = $data3;

        return [
            'code' => 200,
            'data' => [
                'title' => '',
                'xAxis' => $rangeData,
                'series' => $data,
            ],
        ];
    }

    public function actionPaymentAnalysisAreaSpline()
    {
        $from = Yii::$app->request->get('from', date('Y-m-d'));
        $to = Yii::$app->request->get('to', date('Y-m-d', strtotime('tomorrow')));
        $platform = Yii::$app->request->get('platform');
        $gameId = Yii::$app->request->get('gid');
        $server = Yii::$app->request->get('server');

        $platformList = unserialize($platform);
        $serverList = unserialize($server);

        $bars = [
            'pay_money_sum' => '充值金额',
            'pay_man_sum' => '充值人数',
            'active_sum' => '活跃人数',
            'new_pay_money_sum' => '新进充值金额',
            'new_pay_man_sum' => '新进充值人数'
        ];

        $diff_day = intval((strtotime($to) - strtotime($from)) / 86400);

        $rangeTime = range(0, $diff_day - 1);
        $dataAll = $data = $rangeData = [];
        //区间大于一天 查arrange表
        if ($diff_day > 1) {
            $data = [];
            foreach ($bars as $key => $bar) {
                foreach ($rangeTime as $k => $day) {
                    $rangeData[] = date('Y-m-d', strtotime($from.$day.' day'));
                    $f = date('Y-m-d', strtotime($from.$day.' day'));
                    $t = date('Y-m-d', strtotime($from.($day + 1).' day'));
                    $arr = current(Arrange::getDataByServer($f, $t, $gameId, $platformList, $serverList));
                    $data[$key][] = isset($arr[$key]) ? intval($arr[$key]) : 0;
                }
                $dataAll[] = [
                    'name' => $bar,
                    'data' => $data[$key],
                    'visible' => stristr($key, 'new') ? false : true,
                ];
            }
        } else {
            //区间小于一天 直接查payment 表
        }

        return [
            'code' => 200,
            'data' => [
                'title' => '',
                'xAxis' => $rangeData,
                'series' => $dataAll,
            ],
        ];
    }

    public function actionGetPlatformByGame()
    {
        $games = Yii::$app->request->get('originals', null);
        $_g = [];
        if ($games) {
            if (strpos($games, ',') !== false) {
                $_g = explode(',', $games);
            } else {
                $_g = [$games];
            }
        }
        $data = (new Query())->from('game_platform_server as g')
            ->select(['p.id', 'p.name'])
            ->leftJoin('platform p', 'p.id = g.platform_id')
            ->where(['game_id' => $_g])
            ->groupBy('g.platform_id')
            ->all();

        return ['code' => 200, 'data' => $data, 'message' => 'success'];
    }

    public function actionGetServerByPlatform()
    {
        $platform = Yii::$app->request->get('originals', null);
        $game = Yii::$app->request->get('depends', null);
        $_g = [];
        if ($platform) {
            if (strpos($platform, ',') !== false) {
                $_g = explode(',', $platform);
            } else {
                $_g = [$platform];
            }
        }
        $data = (new Query())->from('game_platform_server')
            ->select(['server_id as id', 'server_id as name'])
            ->where(['platform_id' => $_g])
            ->andWhere(['game_id' => $game])
            ->groupBy('platform_id,name')
            ->orderBy('platform_id ASC, name ASC')
            ->all();

        return ['code' => 200, 'data' => $data, 'message' => 'success'];
    }
}