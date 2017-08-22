<?php

namespace common\models;

use common\definitions\Status;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "major".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $game_id
 * @property string  $platform_id
 * @property integer $is_adult
 * @property string  $register_at
 * @property string  $latest_login_at
 * @property integer $login_count
 * @property integer $payment_count
 * @property integer $total_payment_amount
 * @property integer $status
 * @property string  $created_at
 * @property integer $created_by
 * @property string  $updated_at
 * @property integer $updated_by
 */
class Major extends \yii\db\ActiveRecord
{
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
            [['user_id', 'game_id', 'platform_id', 'register_at', 'latest_login_at', 'created_at'], 'required'],
            [
                [
                    'user_id',
                    'game_id',
                    'is_adult',
                    'login_count',
                    'payment_count',
                    'total_payment_amount',
                    'status',
                    'created_by',
                    'updated_by',
                    'platform_id',
                ],
                'integer',
            ],
            [['register_at', 'latest_login_at', 'created_at', 'updated_at'], 'safe'],
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
            'latest_login_at' => 'Latest Login At',
            'login_count' => 'Login Count',
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
                'value' => date('Y-m-d H:i:s'),
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

    public static function getMajor($user_id, $game_id)
    {
        $result = self::find()
            ->where('user_id = :uid', [':uid' => $user_id])
            ->andWhere('game_id = :gid', [':gid' => $game_id])
            ->one();

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
        $mod->latest_login_at = $m->latest_login_at;
        $mod->login_count = $m->login_count;
        $mod->payment_count = $m->payment_count;
        $mod->total_payment_amount = $m->total_payment_amount;
        $mod->status = Status::ACTIVE;

        $mod->save();
    }
}