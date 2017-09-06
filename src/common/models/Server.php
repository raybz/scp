<?php

namespace common\models;

use common\definitions\Status;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "server".
 *
 * @property integer $id
 * @property integer $game_id
 * @property integer $platform_id
 * @property string  $server
 * @property integer $status
 * @property string  $created_at
 */
class Server extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'server';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['game_id', 'platform_id', 'server'], 'required'],
            [['game_id', 'platform_id', 'status'], 'integer'],
            [['created_at',], 'safe'],
            [['server'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'game_id' => 'Game ID',
            'platform_id' => 'Platform ID',
            'server' => 'server',
            'status' => 'Status',
            'created_at' => 'Created At',
        ];
    }
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'value' => date('Y-m-d H:i:s'),
                'updatedAtAttribute' => false,
            ],
        ];
    }

    public function storeData($data)
    {
        $game = Game::getGameByGKey($data->gkey);
        if (!$game){
            return null;
        }
        $platform = Platform::getPlatform($data->platform);
        if (!$platform){
            return null;
        }
        $have = self::getServer($game->id, $platform->id, $data->server_id);
        if (!$have) {
            $mod = new self;
            $mod->game_id = $game->id;
            $mod->platform_id = $platform->id;
            $mod->server = $data->server_id;
            $mod->status = Status::ACTIVE;
            if ($mod->save()) {
                return $mod->id;
            } else{
                var_dump($mod->errors);
            }

        }

        return null;
    }

    public static function getServer($game_id, $platform_id, $server)
    {
        $result = self::find()
            ->where('game_id =:g', [':g' => $game_id])
            ->andWhere('platform_id = :p', [':p' => $platform_id])
            ->andWhere('server = :s',[':s' => $server])
            ->one();

        return $result;
    }

    public static function ServerDataDropData($game_id, $platform_id)
    {
        $res = self::find()
            ->select('server')
            ->where('game_id = :g', [':g' => $game_id])
            ->andWhere(['platform_id' => $platform_id])
            ->orderBy('platform_id,id')
            ->indexBy('id')
            ->column();

        return $res ?: ['empty' => '暂无区服'];
    }
}
