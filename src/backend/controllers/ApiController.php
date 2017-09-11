<?php

namespace backend\controllers;

use common\models\Activity;
use common\models\Arrange;
use common\models\Game;
use common\models\Major;
use common\models\MajorLoginHistory;
use common\models\Payment;
use common\models\Platform;
use Yii;
use yii\db\Query;
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
                'data' => $data,
            ];
        }

        return [
            'code' => 200,
            'data' => [
                'title' => $total,
                'marker' => false,
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
                        $mark = Activity::lineMark($f, $t);

                        $mix = [];
                        if (!empty($mark)) {
                            $aOne = '';
                            $weight = 0;
                            foreach ($mark as $m) {
                                $weight += 1;
                                $aOne[] = ['name' => $m->name ?? '', 'start' => $m->start_at ?? '', 'end' => $m->end_at];
                            }
                            $mix = [
                                'y' => intval(Payment::getPerTimeMoney($game, $f, $t, '', $platformList)),
                                'marker' => [
                                    'fillColor' => 'red',
                                    'states' => [
                                        'hover' => [
                                            'lineColor' => 'red',
                                            'lineWidth' => 4,
                                        ],
                                    ],
                                ],
                                'text' => [
                                    'desc' => $aOne,
                                ],
                            ];
                        }
                        $dataAll[$game]['data'][] = $mark ? $mix : intval(
                            Payment::getPerTimeMoney($game, $f, $t, '', $platformList)
                        );
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
                foreach ($platformList as $k => $platform) {
                    if (is_numeric($platform)) {

                        $g = Platform::findOne($platform);

                        $dataAll[$platform]['name'] = $g['name'];

                        $f = date('Y-m-d', strtotime($from.$day.' day'));
                        $t = date('Y-m-d', strtotime($from.($day + 1).' day'));
                        $pTotal = Arrange::getDataByPlatform($f, $t, $gameList, $platformList);
                        $dataAll[$platform]['data'][] = isset($pTotal[$platform]['pay_money_sum']) ? intval(
                            $pTotal[$platform]['pay_money_sum']
                        ) : 0;

                        $dataAll[$platform]['visible'] = $k > 5 ? false : true;
                    }
                }
            }
            $data = array_values($dataAll);
        }

        return [
            'code' => 200,
            'data' => [
                'title' => '',
                'marker' => false,
                'xAxis' => $rangeData,
                'series' => $data,
            ],
        ];
    }

    public function actionServerPaymentBar()
    {
        $from = Yii::$app->request->post('from', date('Y-m-d'));
        $to = Yii::$app->request->post('to', date('Y-m-d', strtotime('tomorrow')));
        $platform = Yii::$app->request->post('platform', serialize(1));
        $gameId = Yii::$app->request->post('gid', 1001);
        $server = Yii::$app->request->post('server', serialize(1));

        $platformList = unserialize($platform);
        $serverList = unserialize($server);
        $rangeData = [];
        $sl = Arrange::getDataByServer(
            $from,
            $to,
            $gameId,
            $platformList,
            $serverList,
            'platform_id,server_id',
            'pay_money_sum DESC',
            10,
            false
        );
        $res = Arrange::getDataByPlatform($from, $to, $gameId, $platformList);
        $pay_sum_total = [];
        $new_sum_total = [];
        foreach ($res as $re) {
            $pay_sum_total[] = $re['pay_money_sum'];
            $new_sum_total[] = $re['new_sum'];
        }

        $bar = [
            '总充值金额（百分比）',
            '新增用户数（百分比）',
            '活跃用户数',
        ];
        $data = $data1 = $data2 = $data3 = [];
        /* @var $sl Object */
        foreach ($sl->each() as $r) {
            $pf = Platform::findOne($r['platform_id']);
            $rangeData[] = isset($pf->name) ? $pf->name.' / '.$r['server_id'].'区' : '';
            $data1[] = round($r['pay_money_sum'] / array_sum($pay_sum_total) * 100, 2) ?: 0;
            $data2[] = round($r['new_sum'] / array_sum($new_sum_total) * 100, 2) ?: 0;
            $data3[] = intval($r['active_sum']) ?: 0;
        }
        $dl = [
            $data1,
            $data2,
            $data3,
        ];
        foreach ($bar as $k => $b) {
            $data[$k]['name'] = $b;
            $data[$k]['data'] = $dl[$k];
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

    public function actionPaymentAnalysisAreaSpline()
    {
        $from = Yii::$app->request->post('from');
        $to = Yii::$app->request->post('to');
        $platform = Yii::$app->request->post('platform');
        $gameId = Yii::$app->request->post('gid', 1001);
        $server = Yii::$app->request->post('server');

        $platformList = unserialize($platform);
        $serverList = unserialize($server);

        $bars = [
            'pay_money_sum' => '充值金额',
            'pay_man_sum' => '充值人数',
            'active_sum' => '活跃人数',
            'new_pay_money_sum' => '新进充值金额',
            'new_pay_man_sum' => '新进充值人数',
        ];

        $diff_day = intval(ceil((strtotime($to) - strtotime($from)) / 86400));
        $dataAll = $data = $rangeData = $arr = [];
        //区间大于一天 查arrange表
        if ($diff_day > 1) {
            $rangeTime = range(0, $diff_day);
            foreach ($rangeTime as $k => $day) {
                $rangeData[] = date('Y-m-d', strtotime($from.$day.' day'));
                $f = date('Y-m-d', strtotime($from.$day.' day'));
                $t = date('Y-m-d', strtotime($from.($day + 1).' day'));
                $arr = current(Arrange::getDataByServer($f, $t, $gameId, $platformList, $serverList));
                foreach ($bars as $key => $bar) {
                    $data[$key][] = isset($arr[$key]) ? intval($arr[$key]) : 0;
                }
            }
            foreach ($bars as $key => $bar) {
                $dataAll[] = [
                    'name' => $bar,
                    'data' => $data[$key],
                    'visible' => stristr($key, 'new') ? false : true,
                ];
            }
        } else {
            //区间小于一天 直接查payment 表
            $diff_m = intval((strtotime($to) - strtotime($from)) / 3600);
            $rangeTime = range(0, $diff_m);
            foreach ($rangeTime as $k => $hour) {
                $rangeData[] = date('H:i', strtotime($from.$hour.' hour'));
                $f = date('Y-m-d H:i', strtotime($from.$hour.' hour'));
                $t = date('Y-m-d H:i', strtotime($from.($hour + 1).' hour'));
                $wf = date('Y-m-d H:i', strtotime($from.$hour.' hour -1 week'));
                $wt = date('Y-m-d H:i', strtotime($from.($hour + 1).' hour -1 week'));
                $yf = date('Y-m-d H:i', strtotime($from.$hour.' hour -1 day'));
                $yt = date('Y-m-d H:i', strtotime($from.($hour + 1).' hour -1 day'));
                $arr1 = Payment::getPaymentData($gameId, $platformList, $serverList, $f, $t);
                $arr2 = Payment::getPaymentData($gameId, $platformList, $serverList, $yf, $yt);
                $arr3 = Payment::getPaymentData($gameId, $platformList, $serverList, $wf, $wt);

                foreach ($bars as $key => $bar) {
                    $data[0][$key][] = isset($arr1[$key]) ? intval($arr1[$key]) : 0;
                    $data[1][$key][] = isset($arr2[$key]) ? intval($arr2[$key]) : 0;
                    $data[2][$key][] = isset($arr3[$key]) ? intval($arr3[$key]) : 0;
                }
            }
            $bar_day = [
                '当日',
                '前日',
                '上周同期',
            ];
            foreach ($bar_day as $ano => $d) {
                foreach ($bars as $key => $bar) {
                    $dataAll[] = [
                        'name' => $d.$bar,
                        'data' => $data[$ano][$key],
                        'visible' => stristr($key, 'new') ? false : true,
                    ];
                }
            }
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

    public function actionUserSeepLine()
    {
        $from = Yii::$app->request->post('from');
        $to = Yii::$app->request->post('to');
        $platform = Yii::$app->request->post('platform');
        $gameId = Yii::$app->request->post('gid', 1001);
        $server = Yii::$app->request->post('server');
        $type = Yii::$app->request->post('type', 1);
        switch ($type) {
            case 2:
                $name = '周付费率';
                $limit = ' week';
                $lT = 86400 * 7;
                break;
            case 3:
                $name = '月付费率';
                $limit = ' month';
                $lT = 86400 * 30;
                break;
            default:
                $name = '日付费率';
                $limit = ' day';
                $lT = 86400;
                break;
        }
        $platformList = unserialize($platform);
        $serverList = unserialize($server);

        $diff_day = intval(ceil((strtotime($to) - strtotime($from)) / $lT));
        $rangeData = $arr = [];

        if ($diff_day > 1) {
            $rangeTime = range(0, $diff_day);
            foreach ($rangeTime as $k => $day) {
                $rangeData[] = date('Y-m-d', strtotime($from.$day.$limit));
                $f = date('Y-m-d', strtotime($from.$day.$limit));
                $t = date('Y-m-d', strtotime($from.($day + 1).$limit));
                $result = current(Arrange::getDataByServer($f, $t, $gameId, $platformList, $serverList));
                if ($result && ($result['active_sum'] + $result['new_sum']) > 0) {
                    $arr[] = round($result['pay_man_sum'] / ($result['active_sum'] + $result['new_sum']) * 100, 2);
                } else {
                    $arr[] = 0;
                }
            }

        }
        $data = [
            'name' => $name,
            'data' => $arr,
        ];

        return [
            'code' => 200,
            'data' => [
                'title' => '',
                'xAxis' => $rangeData,
                'series' => [$data],
            ],
        ];
    }

    public function actionUserHabitPayFreqBar()
    {
        $from = Yii::$app->request->post('from', date('Y-m-d'));
        $to = Yii::$app->request->post('to', date('Y-m-d', strtotime('tomorrow')));
        $platform = Yii::$app->request->post('platform', serialize(1));
        $gameId = Yii::$app->request->post('gid', 1001);
        $server = Yii::$app->request->post('server', serialize(1));
        $platformList = Json::decode($platform);
        $serverList = Json::decode($server);
        $pays = Payment::payLi($from, $to, $gameId, $platformList, $serverList);
        $data = [];
        $range = [
            1 => '1次',
            2 => '2次',
            3 => '3次',
            4 => '4次',
            5 => '5次',
            10 => '6~10次',
            20 => '11~20次',
            30 => '21~30次',
            40 => '31~40次',
            50 => '41~50次',
            51 => '>50次',
        ];
        foreach ($range as $k => $v) {
            $data[$k] = 0;
        }
        foreach ($pays as $pay) {
            foreach ($range as $k => $r) {
                if (intval($pay['pay_times']) <= $k && intval($pay['pay_times']) < 51) {
                    $data[$k] += 1;
                    break;
                } else {
                    if (intval($pay['pay_times']) >= 51) {
                        $data[51] += 1;
                        break;
                    }
                }
            }
        }

        $rangeData = array_values($range);
        $data['data'] = array_values($data);
        $data['name'] = '充值频次';

        return [
            'code' => 200,
            'data' => [
                'title' => '',
                'xAxis' => $rangeData,
                'series' => [$data],
            ],
        ];
    }

    public function actionUserHabitPayQuotaBar()
    {
        $from = Yii::$app->request->post('from', date('Y-m-d'));
        $to = Yii::$app->request->post('to', date('Y-m-d', strtotime('tomorrow')));
        $platform = Yii::$app->request->post('platform', serialize(1));
        $gameId = Yii::$app->request->post('gid', 1001);
        $server = Yii::$app->request->post('server', serialize(1));
        $platformList = Json::decode($platform);
        $serverList = Json::decode($server);
        $pays = Payment::payLi($from, $to, $gameId, $platformList, $serverList);
        $data = [];
        $range = [
            10 => '0~10',
            50 => '11~50',
            100 => '51~100',
            200 => '101~200',
            500 => '201~500',
            1000 => '501~1000',
            2000 => '1001~2000',
            2001 => '>2000',
        ];
        foreach ($range as $k => $v) {
            $data[$k] = 0;
        }
        foreach ($pays as $pay) {
            foreach ($range as $k => $r) {
                if (intval($pay['pay_total_money']) <= $k && intval($pay['pay_total_money']) < 2001) {
                    $data[$k] += 1;
                    break;
                } else {
                    if (intval($pay['pay_total_money']) >= 2001) {
                        $data[2001] += 1;
                        break;
                    }
                }
            }
        }

        $rangeData = array_values($range);
        $data['data'] = array_values($data);
        $data['name'] = '充值额度';

        return [
            'code' => 200,
            'data' => [
                'title' => '',
                'xAxis' => $rangeData,
                'series' => [$data],
            ],
        ];
    }

    public function actionUserHabitPayGapBar()
    {
        $from = Yii::$app->request->post('from', date('Y-m-d'));
        $to = Yii::$app->request->post('to', date('Y-m-d', strtotime('tomorrow')));
        $platform = Yii::$app->request->post('platform', serialize(1));
        $gameId = Yii::$app->request->post('gid', 1001);
        $server = Yii::$app->request->post('server', serialize(1));
        $platformList = Json::decode($platform);
        $serverList = Json::decode($server);
        $data = $day = [];
        $pays = Payment::find()->where(['>=', 'time', $from])
            ->andWhere(['<=', 'time', $to])
            ->andFilterWhere(['game_id' => $gameId])
            ->andFilterWhere(['platform_id' => $platformList])
            ->andFilterWhere(['server_id' => $serverList]);
        foreach ($pays->each() as $pay) {
            //首次充值
            if (strtotime($pay->last_pay_time) < 0) {
                $day[$pay->id] = 0;
            } else {
                $day[$pay->id] = ceil((strtotime($pay->time) - strtotime($pay->last_pay_time)) / 86400);
            }
        }
        $range = [
            0 => '首次充值',
            1 => '1天',
            3 => '3天',
            5 => '5天',
            7 => '7天',
            30 => '1月',
            90 => '3月',
            180 => '6月',
            366 => '1年',
            367 => '1年以上',
        ];
        foreach ($range as $k => $v) {
            $data[$k] = 0;
        }
        foreach ($day as $d) {
            foreach ($range as $k => $r) {
                if (intval($d) <= $k && intval($d) < 367) {
                    $data[$k] += 1;
                    break;
                } else {
                    if (intval($d) >= 367) {
                        $data[367] += 1;
                        break;
                    }
                }
            }
        }

        $rangeData = array_values($range);
        $data['data'] = array_values($data);
        $data['name'] = '充值间隔';

        return [
            'code' => 200,
            'data' => [
                'title' => '',
                'xAxis' => $rangeData,
                'series' => [$data],
            ],
        ];
    }

    public function actionUserSeepArpLine()
    {
        $from = Yii::$app->request->post('from');
        $to = Yii::$app->request->post('to');
        $platform = Yii::$app->request->post('platform');
        $gameId = Yii::$app->request->post('gid', 1001);
        $server = Yii::$app->request->post('server');
        $type = Yii::$app->request->post('type', 1);
        switch ($type) {
            case 2:
                $name = '周付费率';
                $limit = ' week';
                $lT = 86400 * 7;
                break;
            case 3:
                $name = '月付费率';
                $limit = ' month';
                $lT = 86400 * 30;
                break;
            default:
                $name = '日付费率';
                $limit = ' day';
                $lT = 86400;
                break;
        }
        $platformList = unserialize($platform);
        $serverList = unserialize($server);

        $diff_day = intval(ceil((strtotime($to) - strtotime($from)) / $lT));
        $rangeData = $arr = [];

//        if ($diff_day > 1) {
        $rangeTime = range(0, $diff_day);
        foreach ($rangeTime as $k => $day) {
            $rangeData[] = date('Y-m-d', strtotime($from.$day.$limit));
            $f = date('Y-m-d', strtotime($from.$day.$limit));
            $t = date('Y-m-d', strtotime($from.($day + 1).$limit));
            $result = current(Arrange::getDataByServer($f, $t, $gameId, $platformList, $serverList));
            if ($result) {
                $arr[] = round($result['pay_money_sum'] / $result['pay_man_sum'] * 100, 2);
            } else {
                $arr[] = 0;
            }
        }
//        }
        $data = [
            'name' => $name,
            'data' => $arr,
        ];

        return [
            'code' => 200,
            'data' => [
                'title' => '',
                'xAxis' => $rangeData,
                'series' => [$data],
            ],
        ];
    }

    public function actionPlatformSeepBar()
    {
        $from = Yii::$app->request->post('from', date('Y-m-d'));
        $to = Yii::$app->request->post('to', date('Y-m-d', strtotime('tomorrow')));
        $platform = Yii::$app->request->post('platform');
        $gameId = Yii::$app->request->post('gid', 1001);
        $server = Yii::$app->request->post('server');

        $platformList = unserialize($platform);
        $serverList = unserialize($server);
        $diff = intval(ceil((strtotime($to) - strtotime($from)) / 86400)) ?: 1;
        $rangeData = [];
        $sl = Arrange::getDataByServer(
            $from,
            $to,
            $gameId,
            $platformList,
            $serverList,
            'platform_id',
            'pay_man_sum DESC'
        );

        $bar = [
            '日付费率（百分比）',
            '日均ARUP',
        ];
        $data = $data1 = $data2 = [];
        foreach ($sl as $r) {
            $pf = Platform::findOne($r['platform_id']);
            $rangeData[] = $pf->name ?? '';
            $data1[] = ($r['active_sum'] + $r['new_sum']) > 0 ? round(
                $r['pay_man_sum'] / ($r['active_sum'] + $r['new_sum']) / $diff * 100,
                2
            ) : 0;
            $data2[] = $r['pay_man_sum'] > 0 ? round($r['pay_money_sum'] / $r['pay_man_sum'] / $diff, 2) : 0;
        }
        $dt = [$data1, $data2];
        foreach ($bar as $k => $b) {
            $data[$k]['name'] = $b;
            $data[$k]['data'] = $dt[$k];
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

    public function actionMajorLossDual()
    {
        $from = Yii::$app->request->post('from', date('Y-m-d'));
        $to = Yii::$app->request->post('to', date('Y-m-d', strtotime('tomorrow')));
        $platform = Yii::$app->request->post('platform');
        $gameId = Yii::$app->request->post('gid', 1001);
        $left = $right = $rangeData = [];

        $platformList = unserialize($platform);

        //区间小于一天 直接查payment 表
        $diff_m = intval((strtotime($to) - strtotime($from)) / 86400);
        $rangeTime = range(0, $diff_m);
        foreach ($rangeTime as $k => $day) {
            $rangeData[] = date('Y-m-d', strtotime($from.$day.' day'));
            $f = date('Y-m-d', strtotime($from.$day.' day'));
            $t = date('Y-m-d', strtotime($from.($day + 1).' day'));
            $major = Major::getMajorList($gameId, $platformList, '', $t, true);
            $onMajor = MajorLoginHistory::getMajorOnList($gameId, $platformList, $f, $t, true);
            $outMajor = $major - $onMajor;
            $left[] = $outMajor;
            $right[] = $major > 0 ? round($outMajor / $major * 100, 2) : 0;
        }

        return [
            'code' => 200,
            'data' => [
                'title' => '',
                'xAxis' => $rangeData,
                'series' => [
                    'left' => ['name' => '3日流失数', 'data' => $left, 'unit' => '(人)', 'text' => '3日流失数'],
                    'right' => ['name' => '3日流失率', 'data' => $right, 'unit' => '%', 'text' => '3日流失率'],
                ],
            ],
        ];
    }

    public function actionMajorScatterPlot()
    {
        $from = Yii::$app->request->post('from', date('Y-m-d'));
        $to = Yii::$app->request->post('to', date('Y-m-d', strtotime('tomorrow')));
        $platform = Yii::$app->request->post('platform');
        $gameId = Yii::$app->request->post('gid', 1001);
        $platformList = unserialize($platform);
        $left = $right = [];
        $detail = Major::majorLossDetail($gameId, $platformList, '', $to, $from);
        foreach ($detail as $m) {
            $left[] = [intval($m['login_day']), intval($m['pay_total_money'])];
            $right[] = [intval($m['login_day']), intval($m['pay_total_times'])];
        }

        return [
            'code' => 200,
            'data' => [
                'title' => '流失大户'.count($detail).' 位',
                'format' => [
                    'x' => '天',
                    'y' => '',
                ],
                'series' => [
                    'left' => ['name' => '生命周期/付费金额', 'data' => $left],
                    'right' => ['name' => '生命周期/付费次数', 'data' => $right],
                ],
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
        $data = (new Query())->from('server as g')
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
        $data = (new Query())->from('server')
            ->select(['id', 'server as name'])
            ->where(['platform_id' => $_g])
            ->andWhere(['game_id' => $game])
            ->groupBy('platform_id,name')
            ->orderBy('platform_id ASC, name ASC')
            ->all();

        return ['code' => 200, 'data' => $data, 'message' => 'success'];
    }
}