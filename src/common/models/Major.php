<?php

namespace common\models;

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
                ],
                'integer',
            ],
            [['register_at', 'latest_login_at', 'created_at', 'updated_at'], 'safe'],
            [['platform_id'], 'string', 'max' => 255],
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

    public static function getMajor($user_id, $game_id)
    {
        $result = self::find()
            ->where('user_id = :uid', [':uid' => $user_id])
            ->andWhere('game_id = :gid', [':gid' => $game_id])
            ->one();

        return $result;
    }
}