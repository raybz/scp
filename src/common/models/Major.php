<?php

namespace common\models;

use common\definitions\MajorType;
use common\definitions\Status;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\helpers\Json;

/**
 * This is the model class for table "major".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $game_id
 * @property string  $platform_id
 * @property integer $is_adult
 * @property string  $register_at
 * @property string  $latest_payment_at
 * @property integer $payment_count
 * @property integer $total_payment_amount
 * @property integer $type
 * @property integer $status
 * @property string  $created_at
 * @property integer $created_by
 * @property string  $updated_at
 * @property integer $updated_by
 */
class Major extends \yii\db\ActiveRecord
{
    const THRESHOLD = 3000;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'major';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'game_id', 'platform_id', 'register_at', 'latest_payment_at'], 'required'],
            [
                [
                    'user_id',
                    'game_id',
                    'is_adult',
                    'payment_count',
                    'total_payment_amount',
                    'status',
                    'created_by',
                    'updated_by',
                    'platform_id',
                ],
                'integer',
            ],
            [['register_at', 'created_at', 'updated_at'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'game_id' => 'Game ID',
            'platform_id' => 'Platform ID',
            'is_adult' => 'Is Adult',
            'register_at' => 'Register At',
            'latest_payment_at' => 'Latest Payment At',
            'payment_count' => 'Payment Count',
            'total_payment_amount' => 'Total Payment Amount',
            'status' => 'Status',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'value' =>  function(){
                    return $this->created_at ?: date('Y-m-d H:i:s');
                },
            ],
            [
                'class' => BlameableBehavior::class,
                'value' => function () {
                    if (isset(\Yii::$app->user)) {
                        return \Yii::$app->user->id ?? 0;
                    } else {
                        return 0;
                    }
                },
            ],
        ];
    }

    public function getLoginHistory()
    {
        return $this->hasMany(MajorLoginHistory::className(), ['major_id' => 'id'])
            ->orderBy('latest_login_at');
    }

    public static function getMajor($user_id, $game_id)
    {
        $result = self::find()
            ->where('user_id = :uid', [':uid' => $user_id])
            ->andWhere('game_id = :gid', [':gid' => $game_id])
            ->one();

        return $result;
    }

    public static function getMajorList($game_id, $platform_id, $from = null, $to = null, $out_count = false)
    {
        $query = self::find()
            ->where('game_id = :gid', [':gid' => $game_id])
            ->andWhere(['platform_id'  => $platform_id])
            ->andFilterWhere(['>=', 'register_at', $from])
            ->andFilterWhere(['<', 'register_at', $to]);

        if ($out_count) {
            $result = $query->count();
        } else {
            $result = $query->all();
        }

        return $result;
    }

    public static function majorBackCount($majorList)
    {
        return self::find()->where(['id' => $majorList])->andWhere(['type' => MajorType::BACK])->count();
    }

    public static function majorLoss($game_id, $platform_id, $from, $to, $date)
    {
        $arr = $onArr = [];
        $majorList = self::getMajorList($game_id, $platform_id, $from, $to);
        foreach ($majorList as $major) {
            $arr[] = $major->id;
        }
        $onMajorList = MajorLoginHistory::getMajorOnList(
            $game_id,
            $platform_id,
            $date,
            $to
        );
        foreach ($onMajorList as $major) {
            $onArr[] = $major->major_id;
        }
        $outMajorList = array_diff($arr, $onArr);

        return $outMajorList;
    }

    public static function majorLossPay($game_id, $platform_id, $from, $to, $date)
    {
        $outMajorList = self::majorLoss($game_id, $platform_id, $from, $to, $date);

        return round(MajorLoginHistory::majorTotalPaymentSum($outMajorList) / 100, 2);
    }

    public static function majorLTV($game_id, $platform_id, $from, $to, $date)
    {
        $outMajorList = self::majorLoss($game_id, $platform_id, $from, $to, $date);
        $majorCount = count($outMajorList);

        return $majorCount > 0 ? round(MajorLoginHistory::majorTotalPaymentSum($outMajorList) / $majorCount / 100, 2) : 0;
    }

    public static function majorLossDetail($game_id, $platform_id, $from, $to, $date)
    {
        $outMajorList = self::majorLoss($game_id, $platform_id, $from, $to, $date);
        $lossDetail = MajorLoginHistory::lossMajorLife($outMajorList, '', $to);

        return $lossDetail;
    }

    public static function getMajorOnList($game_id, $platform_id, $from = null, $to = null, $out_count = false)
    {
        $f = date('Y-m-d', strtotime($from.'-3 day'));
        $query = self::find()->alias('m')
            ->leftJoin('major_login_history h', 'h.major_id = m.id')
            ->where('m.game_id = :gid', [':gid' => $game_id])
            ->andWhere(['m.platform_id'  => $platform_id])
            ->andFilterWhere(['>=', 'date', $f])
            ->andFilterWhere(['<', 'date', $to]);
        if ($out_count) {
            $result = $query->count();
        } else {
            $result = $query->all();
        }

        return $result;
    }

    public static function newMajor(Major $m, User $u)
    {
        $mod = new self;
        $mod->user_id = $m->user_id;
        $mod->game_id = $m->game_id;
        $mod->platform_id = $m->platform_id;
        $mod->is_adult = $u->is_adult;
        $mod->register_at = $u->register_at;
        $mod->latest_payment_at = $m->latest_payment_at;
        $mod->payment_count = $m->payment_count;
        $mod->total_payment_amount = $m->total_payment_amount;
        $mod->type = MajorType::NEW;
        $mod->status = Status::ACTIVE;

        $mod->save();
    }

    public  static function saveMajorPay(Major $major, Payment $payment, $money = null)
    {
        $major->payment_count = Payment::getPerTimeMan(
            $payment->game_id,
            '',
            '',
            $payment->user_id,
            $payment->platform_id
        );
        //取分制
        $pMoney = Payment::getPerTimeMoney(
                $payment->game_id,
                '',
                '',
                $payment->user_id,
                $payment->platform_id
            ) * 100;

        $major->total_payment_amount = $money ? intval($money * 100) : intval($pMoney);

        $major->latest_payment_at = $payment->time;

        if ($major->save()) {
            return $major->id;
        } else {
            var_dump(Json::encode($major->errors));exit;
        }
    }

    public  static function newRunMajor(Payment $p, User $user)
    {
        $money = Payment::getPerTimeMoney($p->game_id, '', '', $p->user_id, $p->platform_id);
        //大于等于3000
        if ($money >= self::THRESHOLD) {
            $major = new Major();
            $major->user_id = $p->user_id;
            $major->game_id = $p->game_id;
            $major->platform_id = $p->platform_id;
            $major->is_adult = $user->is_adult;
            $major->type = MajorType::NEW;
            $major->register_at = $user->register_at;
            $major->created_at = $p->time;

            return self::saveMajorPay($major, $p, $money);
        }

        return null;
    }
    
    public static function upType($major_id, $had, $exist, $latest_login_at)
    {
        //一直未登录
        if (!$latest_login_at && !$had) {
            return null;
        }
        $m = Major::findOne($major_id);
        //之前未登录 今日登录
        if (!$had && $latest_login_at) {
            $m->type = MajorType::NEW;
        } //之前有登录 3天内有登录
        elseif ($had && $exist) {
            $m->type = MajorType::ACTIVE;
        } //3天内未登录 今日登录
        elseif (!$exist && $latest_login_at) {
            $m->type = MajorType::BACK;
        } //之前有登录 3天内未登录
        elseif ($had && !$exist) {
            $m->type = MajorType::LOSS;
        }
        if ($m->save()) {
            return $m->id;
        }
        
        return null;
    }
}