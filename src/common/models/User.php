<?php

namespace common\models;

use common\definitions\Status;
use common\definitions\UserIsAdult;
use yii\db\Query;
use yii\helpers\Json;

/**
 * This is the model class for table "user".
 *
 * @property integer $id
 * @property string  $uid
 * @property integer $platform_id
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
            [['uid', 'platform_id', 'register_at', 'created_at'], 'required'],
            [['is_adult', 'status', 'platform_id'], 'integer'],
            [['register_at', 'created_at'], 'safe'],
        ];
    }

    //通过uid,platform 获取用户
    public static function getUser($uid, $platform_id)
    {
        $result = self::find()
            ->where('platform_id = :pid', [':pid' => $platform_id])
            ->andWhere('uid = :uid', [':uid' => $uid])
            ->one();

        return $result;
    }

    public static function getUserList($uid, $platform_id, $field = null)
    {
        $q = self::find();
        if ($field) {
            $q->select($field);
        }

        $result = $q->where(['platform_id' => $platform_id])
            ->andWhere('uid = :uid', [':uid' => $uid])
            ->column();

        return $result;
    }

    public static function saveUser($userData, $origin = null)
    {
        $platform = Platform::getPlatform($userData->platform);
        $user = self::getUser($userData->uid, $platform->id);
        if ($origin) {
            $userData->gid = (Game::getGameByGKey($userData->gkey))->id;
            $userData->time = date('Y-m-d H:i:s', $userData->time);
        }
        if ($user) {
            //新增用户区服
            $server = Server::getServer($userData->gid, $platform->id, $userData->server_id);
            $r = UserGameServerRelation::getUserServer($user->id, $userData->gid, $server->id);
            if(!$r) {
                UserGameServerRelation::addRelation($user, $userData);
            }
            if($user->is_adult != $userData->is_adult && $user->is_adult == UserIsAdult::OTHER) {
                $user->is_adult = $userData->is_adult;
            }
            //更新注册时间
            if (strtotime($user->register_at) > strtotime($userData->time)) {
                $user->register_at = $userData->time;

                $uid = $user->save();

                return $uid->id ?? '';
            } else {
                return '';
            }
        } else {
            return self::newUser($userData);
        }
    }

    public static function newRegister($from, $to, $game_id, $platform_id = null, $server_id = null)
    {
        $result = User::find()->alias('u')
            ->leftJoin('user_game_server_relation s', 's.user_id = u.id')
            ->where(['u.platform_id' => $platform_id])
            ->andFilterWhere(['s.game_id' => $game_id])
            ->andFilterWhere(['s.server_id' => $server_id])
            ->andWhere(['>=', 'u.register_at', $from])
            ->andWhere(['<', 'u.register_at', $to])
            ->andWhere(['u.status' => Status::ACTIVE])
            ->all();

        return $result;
    }

    public static function newUser($user)
    {
        $p = Platform::getPlatform($user->platform);
        $model = new self;
        $model->uid = $user->uid;
        $model->platform_id = $p->id ?: 0;
        $model->is_adult = $user->is_adult ?? UserIsAdult::OTHER;
        $model->register_at = $user->time;
        $model->status = Status::ACTIVE;
        $model->created_at = date('Y-m-d H:i:s');
        if ($model->save()) {
            UserGameServerRelation::addRelation($model, $user);
            return $model->id;
        } else {
            return Json::encode($model->errors);
        }
    }
}
