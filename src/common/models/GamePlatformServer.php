<?php

namespace common\models;

use common\definitions\Status;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "game_platform_server".
 *
 * @property integer $id
 * @property integer $game_id
 * @property integer $platform_id
 * @property string  $server_id
 * @property integer $status
 * @property string  $created_at
 */
class GamePlatformServer extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'game_platform_server';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['game_id', 'platform_id', 'server_id'], 'required'],
            [['game_id', 'platform_id', 'status'], 'integer'],
            [['created_at',], 'safe'],
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
            'game_id' => 'Game ID',
            'platform_id' => 'Platform ID',
            'server_id' => 'server ID',
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
            $mod->server_id = $data->server_id;
            $mod->status = Status::ACTIVE;
            if ($mod->save()) {
                return $mod->id;
            } else{
                var_dump($mod->errors);
            }

        }

        return null;
    }

    public static function getServer($game_id, $platform_id, $server_id)
    {
        $result = self::find()
            ->where('game_id =:g', [':g' => $game_id])
            ->andWhere('platform_id = :p', [':p' => $platform_id])
            ->andWhere('server_id = :s',[':s' => $server_id])
            ->one();

        return $result;
    }

    public static function ServerDataDropData($game_id, $platform_id)
    {
        $res = self::find()
            ->where('game_id = :g', [':g' => $game_id])
            ->andWhere(['platform_id' => $platform_id])
            ->groupBy('server_id')
            ->indexBy('id')
            ->column();

        return $res;
    }
}
