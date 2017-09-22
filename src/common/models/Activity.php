<?php

namespace common\models;

use common\definitions\Status;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "activity".
 *
 * @property integer $id
 * @property string  $name
 * @property integer $game_id
 * @property string  $start_at
 * @property string  $end_at
 * @property string  $desc
 * @property integer $status
 * @property string  $created_at
 * @property integer $created_by
 * @property string  $updated_at
 * @property integer $updated_by
 */
class Activity extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'activity';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['game_id', 'name'], 'required'],
            [['game_id', 'status', 'created_by', 'updated_by'], 'integer'],
            [['start_at', 'end_at', 'created_at', 'updated_at'], 'safe'],
            [['desc'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '名称',
            'game_id' => '游戏',
            'start_at' => '开始时间',
            'end_at' => '结束时间',
            'desc' => '说明',
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

    public static function lineMark($from, $to)
    {
        $result = self::find()->where(['>=', 'start_at', $from])
            ->andWhere(['<', 'start_at', $to])
            ->andWhere(['status' => Status::ACTIVE])
            ->all();

        return $result;
    }
}
