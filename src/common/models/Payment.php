<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\helpers\Json;

/**
 * This is the model class for table "payment".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string  $uid
 * @property integer $platform_id
 * @property string  $platform
 * @property string  $gkey
 * @property string  $gid
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
            [['uid', 'user_id', 'platform', 'platform_id', 'gkey', 'server_id', 'gid','time', 'order_id', 'coins', 'money'], 'required'],
            [['coins', 'gid', 'user_id', 'platform_id'], 'integer'],
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
            'gid' => 'gid',
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
        $game = Game::findOne(['gkey' => $data->gkey]);
        if (!$game) {
            return 'lose gkey';
        }
        $user = User::getUser($data->uid, $data->platform, $game->id);
        if (!$user) {
            return 'lose user';
        }
        $pf = Platform::getPlatform($data->platform);
        if (!$pf) {
            return 'lose platform';
        }
        $model = new self;
        $model->uid = $data->uid;
        $model->user_id = $user->id;
        $model->platform_id = $pf->id;
        $model->platform = $data->platform;
        $model->gkey = $data->gkey;
        $model->gid = $game->id;
        $model->server_id = $data->server_id;
        $model->time = date('Y-m-d H:i:s', (string)$data->time);
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

    public static function getPerTimeMoney($game_id = null, $from = null, $to = null, $user_id = null, $platform_id = null, $server_id = null)
    {
        $from = $from ?: strtotime(date('Y-m-d'));
        $to = $to ?: strtotime(date('Y-m-d').'+1 hour');
        $data = Payment::find()
            ->leftJoin('game g', 'g.id = '.Payment::tableName().'.gid')
            ->andFilterWhere(['>=', 'time', $from])
            ->andFilterWhere(['<', 'time', $to])
            ->andFilterWhere(['user_id' => $user_id])
            ->andFilterWhere([Payment::tableName().'.platform_id' => $platform_id])
            ->andFilterWhere([Payment::tableName().'.server_id' => $server_id])
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
        $data = Payment::find()
            ->leftJoin('game g', 'g.id = '.Payment::tableName().'.gid')
            ->andFilterWhere(['>=', 'time', $from])
            ->andFilterWhere(['<', 'time', $to])
            ->andFilterWhere(['user_id' => $user_id])
            ->andFilterWhere([Payment::tableName().'.platform_id' => $platform_id])
            ->andFilterWhere([Payment::tableName().'.server_id' => $server_id])
            ->andFilterWhere(['g.id' => $game_id])

//            ->createCommand()->rawSql;
            ->count();

        return $data ?? 0;
    }
}
