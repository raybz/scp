<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\Query;
use yii\helpers\Json;

/**
 * This is the model class for table "payment".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $platform_id
 * @property integer $game_id
 * @property integer $server_id
 * @property string  $time
 * @property string  $order_id
 * @property integer $coins
 * @property integer $money
 * @property integer $last_pay_time
 * @property string  $created_at
 */
class Payment extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'payment';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'platform_id', 'server_id', 'game_id', 'time', 'order_id', 'coins', 'money'], 'required'],
            [['coins', 'game_id', 'user_id', 'platform_id', 'server_id'], 'integer'],
            [['money'], 'number'],
            [['created_at', 'time', 'last_pay_time'], 'safe'],
            [['order_id'], 'string', 'max' => 255],
        ];
    }


    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'updatedAtAttribute' => false,
                'value' => date('Y-m-d H:i:s'),
            ],
        ];
    }

    public static function getPayDetail($platform_id, $order_id, $from = null, $to = null)
    {
        $res = self::find()
            ->where('platform_id = :pid', [':pid' => $platform_id])
            ->andWhere('order_id = :oid', [':oid' => $order_id])
            ->andFilterWhere(['>=', 'time', $from])
            ->andFilterWhere(['<', 'time', $to])
//            ->createCommand()->rawSql;
            ->one();

        return $res;
    }

    public static function getUserLastPay($time, $user_id)
    {
        $result = self::find()
            ->where(['<', 'time', $time])
            ->andWhere('user_id = :uid', [':uid' => $user_id])
            ->orderBy('time DESC')
            ->one();

        return $result;
    }

    public static function newData($data)
    {
        $game = Game::find()->where('gkey = :g', [':g' => $data->gkey])->one();
        if (!$game) {
            return 'lose gkey';
        }
        $pf = Platform::getPlatform($data->platform);
        if (!$pf) {
            return 'lose platform';
        }
        $user = User::getUser($data->uid, $pf->id);
        if (!$user) {
            return 'lose user';
        }
        $si = Server::getServer($game->id, $pf->id, $data->server_id);

        $lastPay = self::getUserLastPay($data->time, $user->id);
        $lastPayTime = $lastPay->time ?? 0;
        $model = new self;
        $model->user_id = $user->id;
        $model->platform_id = $pf->id;
        $model->game_id = $game->id;
        $model->server_id = $si->id ?? 0;
        $model->time = $data->time;
        $model->order_id = $data->order_id;
        $model->coins = $data->coins;
        $model->money = $data->money;
        $model->last_pay_time = $lastPayTime;
        if ($model->save()) {

            return $model->id;
        } else {
            return Json::encode($model->errors).Json::encode($model->attributes);
        }
    }

    public static function storeData($data)
    {
        if (!isset($data->platform)) {
            return ['', '', ''];
        }
        $platform = Platform::getPlatform($data->platform);
        $res = self::find()
            ->where('platform_id = :pid', [':pid' => $platform->id])
            ->andWhere('order_id = :oid', [':oid' => $data->order_id])
            ->one();
        if ($res) {
            $lastPay = self::getUserLastPay($res->time, $res->user_id);
            $lastPayTime = $lastPay->time ?? 0;
            if ($lastPayTime && (strtotime($res->last_pay_time) < strtotime($lastPayTime))) {
                $res->last_pay_time = $lastPayTime;
                $res->save();
            } elseif (strtotime($res->last_pay_time) > strtotime($res->time)) {
                $res->last_pay_time = 0;
                $res->save();
            }

            return ['old', $res->id, $res->time];
        } else {
            return ['new', self::newData($data), $data->time];
        }
    }

    public static function getPerTimeMoney($game_id = null, $from = null, $to = null, $user_id = null, $platform_id = null, $server_id = null)
    {
        $data = Payment::find()
            ->alias('p')
            ->leftJoin('game g', 'g.id = p.game_id')
            ->andFilterWhere(['>=', 'time', $from])
            ->andFilterWhere(['<', 'time', $to])
            ->andFilterWhere(['user_id' => $user_id])
            ->andFilterWhere(['p.platform_id' => $platform_id])
            ->andFilterWhere(['p.server_id' => $server_id])
            ->andFilterWhere(['g.id' => $game_id])

//            ->createCommand()->rawSql;
//            ->groupBy(Payment::tableName().'.gid')
            ->sum('money');

        return $data ?? 0;
    }

    public static function getPerTimeMan($game_id = null, $from = null, $to = null, $user_id = null, $platform_id = null, $server_id = null)
    {
        $data = Payment::find()->alias('p')
            ->leftJoin('game g', 'g.id = p.game_id')
            ->andFilterWhere(['>=', 'time', $from])
            ->andFilterWhere(['<', 'time', $to])
            ->andFilterWhere(['user_id' => $user_id])
            ->andFilterWhere(['p.platform_id' => $platform_id])
            ->andFilterWhere(['p.server_id' => $server_id])
            ->andFilterWhere(['g.id' => $game_id])

//            ->createCommand()->rawSql;
            ->count();

        return $data ?? 0;
    }

    public static function getPaymentData(
        $gameId = null,
        $platformList = null,
        $serverList = null,
        $from = null,
        $to = null
    ) {
        $pl = (new Query())->from('payment')
            ->select(
                [
                    '*',
                    'sum(platform_id) as tp',
                    'sum(server_id) as ts',
                    'sum(money) as tMoney',
                    'count(*) as cp',
                ]
            )
            ->andFilterWhere(['>=', 'time', $from])
            ->andFilterWhere(['<', 'time', $to])
            ->andFilterWhere(['game_id' => $gameId])
            ->andFilterWhere(['platform_id' => $platformList])
            ->andFilterWhere(['server_id' => $serverList])
            ->limit(50)
//            ->createCommand()->rawSql;
            ->all();

        $data['platform_id'] = $data['server_id'] = $data['new_sum'] = $data['active_sum'] = $data['pay_money_sum'] = $data['pay_man_sum'] = $data['new_pay_man_sum'] = $data['new_pay_money_sum'] = 0;
        $newUser = [];
        $user = User::newRegister($from, $to, $gameId, $platformList, $serverList);
        foreach ($user as $u) {
            $newUser[] = $u->id;
        }
        $d = current($pl);
        if (!empty($d)){
            $time = ((strtotime($to) - strtotime($from)) / 3600) > 1 ? $from : $from.'/'.$to;
            $user_new_total = count($user);
            $pay_money_sum = $d['tMoney'];
            $pay_man_sum = $d['cp'];
            $data['gid'] = $d['game_id'];
            $data['date'] = $time;
            $data['platform_id'] = $d['tp'];
            $data['server_id'] = $d['ts'];
            $data['new_sum'] = $user_new_total;
            $data['active_sum'] = Arrange::getActive($d, $from, $to);
            $data['pay_money_sum'] = $pay_money_sum ?? 0;
            $data['pay_man_sum'] = $pay_man_sum;
            $data['new_pay_man_sum'] = Payment::getPerTimeMan(
                $d['game_id'],
                $from,
                $to,
                $newUser,
                $d['platform_id'],
                $d['server_id']
            );
            $data['new_pay_money_sum'] = Payment::getPerTimeMoney(
                $d['game_id'],
                $from,
                $to,
                $newUser,
                $d['platform_id'],
                $d['server_id']
            );
        }


        return $data ?: [];
    }

//    public static function latestPayTime($game_id, $user_id)
//    {
//        $result = self::find()
//            ->select('time')
//            ->where('user_id = :uid', [':uid' => $user_id])
//            ->orderBy('time DESC')
//            ->scalar();
//
//        return $result;
//    }

    public static function getMajorPay($from, $to, $user_id)
    {
        $pay = (new Query())->from('payment')
            ->select(
                [
                    '*',
                    'sum(money) pMoney',
                    'count(*) pay_times',
                ]
            )
            ->where(['>=', 'time', $from])
            ->andWhere(['<', 'time', $to])
            ->andWhere('user_id = :uid', [':uid' => $user_id])
            ->groupBy('user_id')
            ->one();

        return $pay;
    }

    public static function payLi($from, $to, $game_id = null, $platform_id = null, $server_id = null)
    {
        $result = (new Query())
            ->select([
                '*',
                'COUNT(*) pay_times',
                'SUM(money) pay_total_money'
            ])
            ->from('payment')
            ->where(['>=', 'time', $from])
            ->andWhere(['<=', 'time', $to])
            ->andFilterWhere(['game_id' => $game_id])
            ->andFilterWhere(['platform_id' => $platform_id])
            ->andFilterWhere(['server_id' => $server_id])
            ->groupBy('user_id')
            ->orderBy('pay_times DESC')
            ->all();

        return $result;
    }
}
