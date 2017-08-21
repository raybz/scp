<?php

namespace common\models;

use console\models\LoginLogTable;
use yii\behaviors\TimestampBehavior;
use yii\db\Query;

/**
 * This is the model class for table "arrange".
 *
 * @property integer $id
 * @property string  $date
 * @property integer $game_id
 * @property integer $platform_id
 * @property string  $server_id
 * @property integer $new
 * @property integer $active
 * @property integer $pay_man
 * @property integer $pay_man_time
 * @property double  $pay_money
 * @property integer $new_pay_man
 * @property double  $new_pay_money
 * @property string  $created_at
 * @property string  $updated_at
 */
class Arrange extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'arrange';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['date', 'game_id', 'platform_id', 'server_id'], 'required'],
            [['date', 'created_at'], 'safe'],
            [['game_id', 'platform_id', 'new', 'active', 'pay_man', 'new_pay_man', 'pay_man_time'], 'integer'],
            [['pay_money', 'new_pay_money'], 'number'],
            [['server_id'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'date' => 'Date',
            'game_id' => 'Game ID',
            'platform_id' => 'Platform ID',
            'server_id' => 'Server ID',
            'new' => 'New',
            'active' => 'Active',
            'pay_man' => 'Pay Man',
            'pay_money' => 'Pay Money',
            'new_pay_man' => 'New Pay Man',
            'new_pay_money' => 'New Pay Money',
            'created_at' => 'Created At',
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'value' => date('Y-m-d H:i:s'),
            ],
        ];
    }

    public static function newData($data)
    {
        $model = new self();
        $model->game_id = $data['game_id'];
        $model->date = $data['date'];
        $model->platform_id = $data['platform_id'];
        $model->server_id = $data['server_id'];
        $model->active = $data['active'];
        $model->new = $data['new'];
        $model->pay_money = $data['pay_money'];
        $model->pay_man = $data['pay_man'];
        $model->pay_man_time = $data['pay_man_time'];
        $model->new_pay_man = $data['new_pay_man'];
        $model->new_pay_money = $data['new_pay_money'];
        if ($model->save()) {
            return $model->id;
        }

        return null;
    }

    public static function storeData($data)
    {
        $model = self::find()
            ->where('date = :date', [':date' => $data['date']])
            ->andWhere('game_id = :gid', [':gid' => $data['game_id']])
            ->andWhere('platform_id = :p', [':p' => $data['platform_id']])
            ->andWhere('server_id = :s', [':s' => $data['server_id']])
            ->one();

        if ($model) {
            $model->game_id = $data['game_id'];
            $model->date = $data['date'];
            $model->platform_id = $data['platform_id'];
            $model->server_id = $data['server_id'];
            $model->active = $data['active'];
            $model->new = $data['new'];
            $model->pay_money = $data['pay_money'];
            $model->pay_man = $data['pay_man'];
            $model->pay_man_time = $data['pay_man_time'];
            $model->new_pay_man = $data['new_pay_man'];
            $model->new_pay_money = $data['new_pay_money'];
            if ($model->save()) {
                return $model->id;
            }

            return null;
        } else {
            return self::newData($data);
        }
    }

    public static function getActive($data, $f, $t)
    {
        LoginLogTable::$month = date('Ym', strtotime($f));
        //查询是否登录过
        $platform = Platform::findOne($data['platform_id']);
        $server = Server::findOne($data['server_id']);
        $login = LoginLogTable::find()
            ->where(['>=', 'time', $f])
            ->andWhere(['<', 'time', $t])
            ->andWhere('gid = :g', [':g' => $data['game_id']])
            ->andWhere('platform = :p', [':p' => $platform->abbreviation ?? ''])
            ->andWhere('server_id = :s', [':s' => $server->server ?? ''])
            ->groupBy('uid,platform,server_id');
        $active = [];
        if ($login->one()) {
            foreach ($login->each(100) as $l) {
                $user = User::find()
                    ->where('register_at' < $f)
                    ->andWhere('uid = :uid', [':uid' => $l->uid])
                    ->andWhere('platform = :p', [':p' => $l->platform])
                    ->andWhere('server_id = :s', [':p' => $l->server_id])
                    ->andWhere('gid = :g', [':g' => $l->gid]);
                if ($user) {
                    $active[] = $user;
                }
            }
        }

        return count($active);
    }


    public static function getDataByPlatform($from, $to, $gid = null,$platform_id = null, $groupBy = null, $indexBy = null, $limit = null)
    {
        $query = (new Query())->from('arrange')
            ->select([
                'date',
                'game_id',
                'platform_id',
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
            ->andFilterWhere(['game_id' => $gid])
            ->andFilterWhere(['platform_id' => $platform_id]);
        if (!$groupBy) {
            $query->groupBy('platform_id');
        } else {
            $query->groupBy($groupBy);
        }
        if (!$indexBy) {
            $query->indexBy('platform_id');
        } else {
            $query->indexBy($platform_id);
        }
        if ($limit) {
            $query->limit($limit);
        }
        $result = $query->all();

        return $result;
    }

    public static function getDataByServer($from, $to, $gid = null, $platform_id = null, $serverList = null, $groupBy = null, $orderBy = null, $limit = null)
    {
        $sl = (new Query())->from('arrange')
            ->select(
                [
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
                ]
            )
            ->where(
                'date >= :from AND date < :to',
                [
                    ':from' => $from,
                    ':to' => $to,
                ]
            )
            ->andFilterWhere(['game_id' => $gid])
            ->andFilterWhere(['platform_id' => $platform_id])
            ->andFilterWhere(['server_id' => $serverList]);
        if (!$groupBy) {
            $sl->groupBy('game_id');
        } else {
            $sl->groupBy($groupBy);
        }
        if (!$orderBy) {
            $sl->orderBy('pay_money_sum DESC');
        } else {
            $sl->orderBy($orderBy);
        }
        if ($limit) {
            $sl->limit($limit);
        }
        $result = $sl->all();

        return $result;
    }

}