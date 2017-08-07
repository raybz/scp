<?php

namespace common\models;

use common\definitions\Status;
use console\models\LoginLogTable;

/**
 * This is the model class for table "user".
 *
 * @property integer $id
 * @property string  $uid
 * @property string  $platform
 * @property string  $gkey
 * @property integer $gid
 * @property string  $server_id
 * @property integer $is_adult
 * @property string  $register_at
 * @property integer $status
 * @property string  $created_at
 */
class User extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid', 'platform', 'gkey', 'gid', 'register_at', 'created_at'], 'required'],
            [['gid', 'is_adult', 'status'], 'integer'],
            [['register_at', 'created_at'], 'safe'],
            [['uid', 'platform', 'gkey', 'server_id'], 'string', 'max' => 255],
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
            'gid' => 'Gid',
            'server_id' => 'Server ID',
            'is_adult' => 'Is Adult',
            'register_at' => 'Register At',
            'status' => 'Status',
            'created_at' => 'Created At',
        ];
    }


    public static function getUser($uid, $platform, $gid)
    {
        $result = self::find()
            ->where('uid = :uid', [':uid' => $uid])
            ->andWhere('platform = :platform', [':platform' => $platform])
            ->andWhere('gid = :gid', [':gid' => $gid])
            ->one();

        return  $result;
    }

    public static function newRegister($from, $to, $gid)
    {
        $result = self::find()
            ->where(['gid' => $gid])
            ->andWhere(['>=', 'register_at', $from])
            ->andWhere(['<', 'register_at', $to])
            ->andWhere(['status' => Status::ACTIVE])
            ->all();

        return $result;
    }

    public static function newUser(LoginLogTable $user)
    {
        $model = new self;
        $model->uid = $user->uid;
        $model->platform = $user->platform;
        $model->gkey = $user->gkey;
        $model->gid = $user->gid;
        $model->server_id = $user->server_id;
        $model->is_adult = $user->is_adult;
        $model->register_at = date('Y-m-d H:i:s', $user->time);
        $model->status = Status::ACTIVE;
        $model->created_at = date('Y-m-d H:i:s');

        if ($model->save()) {
            return $model->id;
        }

        return null;
    }
}
