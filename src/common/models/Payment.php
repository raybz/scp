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
            [['created_at'], 'safe'],
            [['time', 'order_id'], 'string', 'max' => 255],
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

    public static function newData($data)
    {
        $game = Game::findOne(['gkey' => $data->gkey]);
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
        $model = new self;
        $model->user_id = $user->id;
        $model->platform_id = $pf->id;
        $model->game_id = $game->id;
        $model->server_id = $si->id ?? 0;
        $model->time = $data->time;
        $model->order_id = $data->order_id;
        $model->coins = $data->coins;
        $model->money = $data->money;
        if ($model->save()) {
            return $model->id;
        } else {
            return Json::encode($model->errors).Json::encode($model->attributes);
        }
    }

    public static function storeData($data)
    {
        $platform = Platform::getPlatform($data->platform);
        $res = self::find()
            ->select('id, time')
            ->where('platform_id = :pid', [':pid' => $platform->id])
            ->andWhere('order_id = :oid', [':oid' => $data->order_id])
            ->one();
        if ($res) {
            return ['old', $res->id, $res->time];
        } else {
            return ['new', self::newData($data), $data->time];
        }
    }

    public static function getPerTimeMoney($game_id = null, $from = null, $to = null, $user_id = null, $platform_id = null, $server_id = null)
    {
        $from = $from ?: strtotime(date('Y-m-d'));
        $to = $to ?: strtotime(date('Y-m-d').'+1 hour');
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
        $from = $from ?: strtotime(date('Y-m-d'));
        $to = $to ?: strtotime(date('Y-m-d').'+1 hour');
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
            ->andFilterWhere(['game_id' => $gameId])
            ->andFilterWhere(['platform_id' => $platformList])
            ->andFilterWhere(['server_id' => $serverList])
            ->andFilterWhere(['>=', 'time', $from])
            ->andFilterWhere(['<', 'time', $to])
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
}
