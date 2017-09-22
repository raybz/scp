<?php

namespace common\models;

/**
 * This is the model class for table "online_data".
 *
 * @property integer $id
 * @property integer $game_id
 * @property string  $date
 * @property double  $avg_online
 * @property double  $max_online
 * @property string  $created_at
 */
class OnlineData extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'online_data';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['date', 'created_at', 'game_id'], 'required'],
            [['date', 'created_at'], 'safe'],
            [['avg_online', 'max_online'], 'number'],
            [['game_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'game_id' => 'game_id',
            'date' => 'Date',
            'avg_online' => 'Avg Online',
            'max_online' => 'Max Online',
            'created_at' => 'Created At',
        ];
    }

    public static function newData($data)
    {
        $mod = new self;
        $mod->date = $data['date'];
        $mod->game_id = $data['game_id'];
        $mod->avg_online = $data['avg_online'];
        $mod->max_online = $data['max_online'];
        $mod->created_at = date('Y-m-d H:i:s');
        if ($mod->save()) {
            return $mod->id;
        }

        return null;
    }

    public static function storeData($data)
    {
        $exMod = self::getData($data['date'], $data['game_id']);
        if ($exMod) {
            $exMod->avg_online = $data['avg_online'];
            $exMod->max_online = $data['max_online'];
            if ($exMod->save()) {
                return ['update', $exMod->id];
            }

            return ['error', ''];
        } else {
            return ['new', self::newData($data)];
        }
    }

    public static function getData($date, $game_id)
    {
        return self::find()
            ->where('date = :date', [':date' => $date])
            ->andWhere('game_id =:gid', [':gid' => $game_id])
            ->one();
    }
}
