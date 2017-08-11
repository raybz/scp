<?php

namespace common\models;

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
            [['game_id', 'platform_id', 'new', 'active', 'pay_man', 'new_pay_man'], 'integer'],
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

    public static function getDataByPlatform($from, $to, $gid = null,$platform_id = null, $groupBy = null, $indexBy = null)
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

        $result = $query->all();

        return $result;
    }
    public static function getOneDataByGame($from, $to, $gid = null,$groupBy = 'game_id')
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
            ->andFilterWhere(['game_id' => $gid]);
            $query->groupBy($groupBy);

        $result = $query->one();

        return $result;
    }
}