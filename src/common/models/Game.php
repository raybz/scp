<?php

namespace common\models;

use common\definitions\Status;

/**
 * This is the model class for table "game".
 *
 * @property integer $id
 * @property string  $gkey
 * @property string  $name
 * @property integer $status
 * @property string  $created_at
 * @property string  $updated_at
 */
class Game extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'game';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['gkey', 'name', 'created_at'], 'required'],
            [['status'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['gkey'], 'string', 'max' => 10],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'gkey' => 'Gkey',
            'name' => 'Name',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public static function gameList()
    {
        $res = self::find()
            ->select(['id', 'gkey', 'name'])
            ->where(['status' => Status::ACTIVE])
            ->all();

        return $res ?: [];
    }

    public static function getGameByGKey($gKey)
    {
        $result = self::find()
            ->where(['status' => Status::ACTIVE])
            ->andWhere('gkey = :gk', [':gk' => $gKey])
            ->one();

        return $result;
    }

    public static function gameDropDownData()
    {
        $res = self::find()
            ->select(['name'])
            ->where(['status' => Status::ACTIVE])
            ->indexBy('id')
            ->orderBy('id')
            ->column();

        return $res ?: ['empty' => '暂无游戏'];
    }
}
