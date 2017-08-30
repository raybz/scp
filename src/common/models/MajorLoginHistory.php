<?php

namespace common\models;

use yii\helpers\Json;


/**
 * This is the model class for table "major_login_history".
 *
 * @property integer $id
 * @property string  $date
 * @property integer $major_id
 * @property double  $money
 * @property integer $pay_times
 * @property string  $latest_login_at
 * @property integer $login_count
 */
class MajorLoginHistory extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'major_login_history';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['date', 'latest_login_at'], 'safe'],
            [['major_id', 'money'], 'required'],
            [['major_id', 'pay_times', 'login_count'], 'integer'],
            [['money'], 'number'],
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
            'major_id' => 'Major ID',
            'money' => 'Money',
            'pay_times' => 'Pay Times',
            'latest_login_at' => 'Latest Login At',
        ];
    }

    public static function getMajorHistory($date, $major_id)
    {
        $result = self::find()->where('date = :d', [':d' => $date])
            ->andWhere('major_id = :mid', [':mid' => $major_id])
            ->one();

        return $result;
    }

    public static function getMajorHistoryExist($major_id, $to, $from = null)
    {
        $result = self::find()
            ->where(' date < :t', [':t' => $to])
            ->andFilterWhere(['>=', 'date', $from])
            ->andWhere('major_id = :mid', [':mid' => $major_id])
            ->one();

        return $result;
    }

    public static function newData($data)
    {
        $mod = new self();
        $mod->date = $data['date'];
        $mod->major_id = $data['major_id'];
        $mod->money = $data['money'];
        $mod->pay_times = $data['pay_times'];
        $mod->latest_login_at = $data['latest_login_at'];
        $mod->login_count = $data['login_count'];
        if ($mod->save()) {
            return $mod->id;
        } else {
            return Json::encode($mod->errors);
        }

        return null;
    }

    public static function storeData($data)
    {
        $major = self::getMajorHistory($data['date'], $data['major_id']);
        if ($major) {
            $major->money = $data['money'];
            $major->pay_times = $data['pay_times'];
            $major->latest_login_at = $data['latest_login_at'];
            $major->login_count = $data['login_count'];
            if ($major->save()) {
                return $major->id;
            } else {
                return Json::encode($major->errors);
            }
        } else {
            return self::newData($data);
        }

        return null;
    }

    public static function getMajorOnList($game_id, $platform_id, $from = null, $to = null, $out_count = false)
    {
        $f = date('Y-m-d', strtotime($from.'-2 day'));
        $query = self::find()->alias('h')
            ->leftJoin('major m', 'h.major_id = m.id')
            ->where('m.game_id = :gid', [':gid' => $game_id])
            ->andWhere(['m.platform_id'  => $platform_id])
            ->andFilterWhere(['>=', 'date', $f])
            ->andFilterWhere(['<', 'date', $to])
            ->groupBy('major_id');
        if ($out_count) {
            $result = $query->count();
        } else {
            $result = $query->all();
        }

        return $result;
    }

    public static function majorHistoryDetail($game_id, $platform_id, $from = null, $to = null, $out_count = false)
    {
        $query = self::find()->alias('h')
            ->select('h.*')
            ->leftJoin('major m', 'h.major_id = m.id')
            ->where('m.game_id = :gid', [':gid' => $game_id])
            ->andWhere(['m.platform_id'  => $platform_id])
            ->andFilterWhere(['>=', 'date', $from])
            ->andFilterWhere(['<', 'date', $to]);
        if ($out_count) {
            $result = $query->count();
        } else {
            $result = $query->all();
        }

        return $result;
    }

    public static function perDayMajor($date)
    {
        return self::find()
            ->select('major_id')
            ->where('date = :d', [':d' => $date])
            ->column();
    }

    public static function majorTotalLoginCount($major_id)
    {
        return self::find()
            ->where(['major_id' => $major_id])
            ->count();
    }

    public static function majorTotalPaymentSum($major_id)
    {
        return self::find()
            ->where(['major_id' => $major_id])
            ->sum('money');
    }
}