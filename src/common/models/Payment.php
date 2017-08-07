<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\helpers\Json;

/**
 * This is the model class for table "payment".
 *
 * @property integer $id
 * @property integer $uid
 * @property string  $platform
 * @property string  $gkey
 * @property string  $server_id
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
            [['uid', 'platform', 'gkey', 'server_id', 'time', 'order_id', 'coins', 'money'], 'required'],
            [['coins'], 'integer'],
            [['money'], 'number'],
            [['created_at'], 'safe'],
            [['uid', 'platform', 'gkey', 'server_id', 'time', 'order_id'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'uid' => 'Uid',
            'platform' => 'Platform',
            'gkey' => 'Gkey',
            'server_id' => 'Server ID',
            'time' => 'Time',
            'order_id' => 'Order ID',
            'coins' => 'Coins',
            'money' => 'Money',
            'created_at' => 'Created At',
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
        $model = new self;
        $model->uid = $data->uid;
        $model->platform = $data->platform;
        $model->gkey = $data->gkey;
        $model->server_id = $data->server_id;
        $model->time = (string)$data->time;
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
        $res = self::find()->select('id')->where('uid=:uid',[':uid' => $data->uid])
            ->andWhere('platform = :p', [':p' => $data->platform])
            ->andWhere('order_id = :o', [':o' => $data->order_id])
            ->one();
        if ($res) {
            return ['old', $res->id];
        } else {
            return ['new', self::newData($data)];
        }
    }

    public static function getPerTimeMoney($from = null, $to = null, $game_id = null)
    {
        $from = $from ?: strtotime(date('Y-m-d'));
        $to = $to ?: strtotime(date('Y-m-d').'+1 hour');
        $data = Payment::find()
            ->leftJoin('game g', 'g.gkey = '.Payment::tableName().'.gkey')
            ->where('time >= :from', [':from' => $from])
            ->andWhere('time < :to', [':to' => $to])
            ->andFilterWhere(['g.id' => $game_id])
            ->groupBy(Payment::tableName().'.gkey')
            ->sum('money');

        return $data ?? 0;
    }

    public static function getPerTimeMan($from = null, $to = null, $game_id = null)
    {
        $from = $from ?: strtotime(date('Y-m-d'));
        $to = $to ?: strtotime(date('Y-m-d').'+1 hour');
        $data = Payment::find()
            ->leftJoin('game g', 'g.gkey = '.Payment::tableName().'.gkey')
            ->where('time >= :from', [':from' => $from])
            ->andWhere('time < :to', [':to' => $to])
            ->andFilterWhere(['g.id' => $game_id])
            ->groupBy(['payment.platform', 'payment.uid'])
//            ->createCommand()->rawSql;
            ->count();

        return $data ?? 0;
    }
}
