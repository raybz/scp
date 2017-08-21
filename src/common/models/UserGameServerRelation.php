<?php

namespace common\models;

use console\models\LoginLogTable;

/**
 * This is the model class for table "user_game_server_relation".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $game_id
 * @property integer $server_id
 * @property string  $created_at
 */
class UserGameServerRelation extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_game_server_relation';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'game_id', 'server_id', 'created_at'], 'required'],
            [['user_id', 'game_id', 'server_id'], 'integer'],
            [['created_at'], 'safe'],
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
            'server_id' => 'Server ID',
            'created_at' => 'Created At',
        ];
    }

    public static function addRelation(User $user,LoginLogTable $data)
    {
        $server = Server::getServer($data->gid, $user->platform_id, $data->server_id);
        $mod = new self();
        $mod->user_id = $user->id;
        $mod->game_id = $data->gid;
        $mod->server_id = $server->id ?? 0;
        $mod->created_at = date('Y-m-d H:i:s');

        if ($id = $mod->save()){
            return $id;
        } else {
            var_dump($mod->errors);exit;
        }
    }


    public static function getUserServer($user_id, $game_id, $server_id)
    {
        $result = self::find()
            ->where('user_id = :uid', [':uid' => $user_id])
            ->andWhere('game_id = :gid', [':gid' => $game_id])
            ->andWhere('server_id = :sid', [':sid' => $server_id])
            ->one();

        return $result;
    }
}