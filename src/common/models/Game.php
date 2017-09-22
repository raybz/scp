<?php

namespace common\models;

use common\definitions\Status;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "game".
 *
 * @property integer $id
 * @property string  $gkey
 * @property string  $name
 * @property integer $status
 * @property string  $created_at
 * @property integer $created_by
 * @property string  $updated_at
 * @property integer $updated_by
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
            [['gkey', 'name'], 'required'],
            [['status', 'created_by', 'updated_by'], 'integer'],
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
            ],
        ];
    }

    public static function gameList()
    {
        $res = self::find()
            ->select(['id', 'gkey', 'name'])
            ->where(['status' => Status::ACTIVE])
            ->asArray()
            ->all();

        return $res ?: [];
    }

    public static function getGameByGKey($gKey)
    {
        $result = self::find()
            ->where('gkey = :gk', [':gk' => $gKey])
            ->andWhere(['status' => Status::ACTIVE])
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
