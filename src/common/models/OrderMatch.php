<?php

namespace common\models;

use common\definitions\OrderMatchType;
use yii\helpers\Json;

/**
 * This is the model class for table "order_match".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $game_id
 * @property integer $platform_id
 * @property integer $server_id
 * @property string  $time
 * @property string  $order_id
 * @property integer $coins
 * @property double  $money
 * @property integer $type
 * @property string  $created_at
 * @property integer $batch
 */
class OrderMatch extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'order_match';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                [
                    'user_id',
                    'game_id',
                    'platform_id',
                    'server_id',
                    'time',
                    'order_id',
                    'coins',
                    'money',
                    'created_at',
                    'batch',
                ],
                'required',
            ],
            [['user_id', 'game_id', 'platform_id', 'server_id', 'coins', 'type', 'batch'], 'integer'],
            [['time', 'created_at'], 'safe'],
            [['money'], 'number'],
            [['order_id'], 'string', 'max' => 255],
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
            'server_id' => 'Server ID',
            'time' => 'Time',
            'order_id' => 'Order ID',
            'coins' => 'Coins',
            'money' => 'Money',
            'type' => 'Type',
            'created_at' => 'Created At',
        ];
    }


    protected static function getFileLine($file_name, $line)
    {
        $n = 0;
        $out = '';

        $handle = fopen($file_name, 'r');
        if ($handle) {
            while (!feof($handle)) {
                ++$n;
                $out = fgets($handle, 4096);
                if ($line == $n) {
                    break;
                }
            }
            fclose($handle);
        }
        if ($line == $n) {
            $content = mb_convert_encoding($out, "utf-8", "gb2312");

            return $content;
        }
        return false;
    }

    public static function fileContext($file_name, $platform_id, $from, $to, $batch)
    {
        $i = 2;
        do {
            $data = self::getFileLine($file_name, $i);
            if(!boolval($data)){
                continue;
            }
            echo $data.PHP_EOL;
            $e = explode(',', $data);
            $result = Payment::getPayDetail(intval($platform_id), strtolower(trim($e[1], ' ')), $from, $to);
            if ($result) {
                $hav = self::getOrderMatch($result, 1);
                if(!$hav){
                    $id = self::saveOrderMatch($result, OrderMatchType::ALL_HAD, $batch);
                    echo 'new ID: '.$id.PHP_EOL;
                } else{
                    echo 'old ID: '.$hav->id.PHP_EOL;
                }
            }
            $i++;
        } while (boolval($data));

        return true;
    }



    public static function weH($file_name, $platform_id, $from, $to, $batch)
    {
        $payment = Payment::find()
            ->where('platform_id = :pid', [':pid' => $platform_id])
            ->andWhere(['>=', 'time', $from])
            ->andWhere(['<', 'time', $to]);
        $i =2;
        do {
            $data = self::getFileLine($file_name, $i);
            if(!boolval($data)){
                continue;
            }
            $e = explode(',', $data);
//            $a = trim($e[0], '"');
//            $a = str_replace("\t", "",$a);
//            $order_id =  $a;
            $order_id =  strtolower(trim($e[1], ' '));
            echo $order_id.PHP_EOL;
            $arr[] = $order_id;

            $i++;
        } while (boolval($data));
        $pArr = [];
        foreach ($payment->each() as $pay) {
//            if (!in_array($pay->order_id, $arr)) {
//                $hav = self::getOrderMatch($pay, $batch);
//                if($hav){
//                        echo 'old ID: '.$hav->id.PHP_EOL;
//                    continue;
//                }
//
//                $id = self::saveOrderMatch($pay, OrderMatchType::OTHER_LOSE, $batch);
//                echo 'new ID: '.$id.PHP_EOL;
//            } else{
//                echo 'ha'.PHP_EOL;
//            }
            $pArr[] = $pay->order_id;
        }
//        $diff = array_diff($pArr, $arr);
        $diff = array_diff($arr, $pArr);
        var_dump($diff);
        return true;
    }

    public static function saveOrderMatchFile($file, $content)
    {
        $handel = fopen($file, 'a');
        fwrite($handel, $content);
        fclose($handel);
    }

    public static function saveOrderMatch(Payment $p, $type, $batch)
    {
        $mod = new self;
        $mod->user_id = $p->user_id;
        $mod->game_id = $p->game_id;
        $mod->platform_id = $p->platform_id;
        $mod->server_id = $p->server_id;
        $mod->time = $p->time;
        $mod->order_id = $p->order_id;
        $mod->coins = $p->coins;
        $mod->money = $p->money;
        $mod->type = $type;
        $mod->created_at = date('Y-m-d H:i:s');
        $mod->batch = $batch;

        if ($mod->save()) {
            return $mod->id;
        }

        return null;
    }

    public static function getOrderMatch(Payment $data, $batch)
    {
        $result = self::find()
            ->where('platform_id = :pid', [':pid' => $data->platform_id])
            ->andWhere('order_id = :oid', [':oid' => $data->order_id])
            ->andWhere('batch = :t', [':t' => $batch])
            ->one();

        return $result;
    }
}
