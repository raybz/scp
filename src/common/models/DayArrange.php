<?php

namespace common\models;

/**
 * This is the model class for table "day_arrange".
 *
 * @property integer $id
 * @property integer $gid
 * @property string  $date
 * @property integer $register
 * @property integer $max_online
 * @property integer $avg_online
 * @property integer $pay_money_sum
 * @property integer $pay_man_sum
 * @property string  $created_at
 */
class DayArrange extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'day_arrange';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['gid', 'date'], 'required'],
            [['gid', 'register', 'max_online', 'avg_online', 'pay_money_sum', 'pay_man_sum'], 'integer'],
            [['date', 'created_at'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'gid' => '游戏',
            'date' => '日期',
            'register' => '注册人数',
            'max_online' => '最高在线人数',
            'avg_online' => '平均在线人数',
            'pay_money_sum' => '充值金额',
            'pay_man_sum' => '充值人数',
            'created_at' => 'Created At',
        ];
    }

    public static function storeData($data)
    {
        $had = self::getData($data['gid'], $data['date']);
        if ($had) {
            return $had['id'];
        }
        $model = new self();
        $model->gid = $data['gid'];
        $model->date = $data['date'];
        $model->register = $data['register'];
        $model->max_online = $data['max_online'];
        $model->avg_online = $data['avg_online'];
        $model->pay_money_sum = $data['pay_money_sum'];
        $model->pay_man_sum = $data['pay_man_sum'];
        $model->created_at = date('Y-m-d H:i:s');
        if ($model->save()) {
            return $model->id;
        }

        return null;
    }

    public static function getData($gid, $date)
    {
        $result = self::find()
            ->where('gid = :gid',[':gid' => $gid])
            ->andWhere('date = :date', [':date' => $date])
            ->one();

        return $result;
    }

    public static function getColumnSum($column, $from, $to, $gid)
    {
        $q = self::find()
            ->where('gid = :gid',[':gid' => $gid])
            ->andWhere(['>=', 'date', $from])
            ->andWhere(['<=', 'date', $to])
            ->sum($column);

        return $q ?: 0;
    }
}