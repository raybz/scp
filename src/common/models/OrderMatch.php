<?php

namespace common\models;

use common\definitions\OrderMatchType;

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

    //按行获取文件
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

    //保存输出文件
    protected static function saveOrderMatchFile($file, $content)
    {
        $handel = fopen($file, 'a');
        fwrite($handel, $content);
        fclose($handel);
    }

    /**
     * fileMatch 两csv 对比 (file_name2 比 file_name 多出部分输出)
     * @param $file_name
     * @param $order_column
     * @param $file_name2
     * @param $order_column2
     * @param $output_file_name
     */
    public static function fileMatch($file_name, $order_column,$file_name2, $order_column2, $output_file_name)
    {
        $filename = \Yii::getAlias('@backend').'/file/'.$output_file_name;
        $line = 2;
        do {
            $data = self::getFileLine($file_name, $line);
            if (!boolval($data)) {
                continue;
            }
            $e = explode(',', $data);
            echo $e[$order_column].PHP_EOL;
            $arr2[] = $e[$order_column];

            $line++;
        } while (boolval($data));

        $line = 2;
        do {
            $data = self::getFileLine($file_name2, $line);
            if (!boolval($data)) {
                continue;
            }

            $e = explode(',', $data);
            echo $e[$order_column2].PHP_EOL;

            if (!in_array($e[$order_column2], $arr2)) {
                $data = self::getFileLine($file_name, $line);
                $e = explode(',', $data);
                echo 'more: '.$e[$order_column2].PHP_EOL;
                if (isset($e[$order_column2])) {
                    $e[$order_column2] = "\t".$e[$order_column2];
                }
                $str = implode(',', $e);
                self::saveOrderMatchFile($filename, $str);
            }
            $line++;
        } while (boolval($data));
    }

    /**
     * getRepeat 单文件获取重复订单
     * @param     $file_name [源文件]
     * @param int $order_column 订单所在的列
     * @param     $output_file_name [输出文件名]
     */
    public static function getRepeat($file_name,int $order_column, $output_file_name)
    {
        $filename = \Yii::getAlias('@backend').'/file/'.$output_file_name;
        $line = 2;
        do {
            $data = self::getFileLine($file_name, $line);
            if (!boolval($data)) {
                continue;
            }

            $e = explode(',', $data);
            echo $e[$order_column].PHP_EOL;

            if (isset($have[$e[$order_column]])) {
                $have[$e[$order_column]] = $line;
            } else {
                $have[$e[$order_column]] = 1;
            }
            if ($have[$e[$order_column]] > 1) {
                $data = self::getFileLine($file_name, $line);
                $e = explode(',', $data);
                echo 'repeat : '.$e[$order_column].PHP_EOL;
                if (isset($e[$order_column])) {
                    $e[$order_column] = "\t".$e[$order_column];
                }
                $str = implode(',', $e);
                self::saveOrderMatchFile($filename, $str);
            }
            $line ++;
        } while (boolval($data));
    }

    //导出库内数据到csv
    public static function orderOut($game_id, $platform_id, $from, $to, $output_file_name)
    {
        $q = Payment::find();
        $result = $q->where(['>=', 'time', $from])
            ->andWhere(['<', 'time', $to])
            ->andWhere('game_id = :gid', [':gid' => $game_id])
            ->andWhere(['platform_id' => $platform_id]);
        $header = 'ID'.','.'订单'.','.'用户ID'.','.'游戏名'.','.'平台'.','.'元宝'.','.'金额'.','.'时间'.PHP_EOL;
        $filename = \Yii::getAlias('@backend').'/file/'.$output_file_name;
        self::saveOrderMatchFile($filename, $header);
        foreach ($result->each() as $k => $p) {

            $game = Game::findOne($game_id);
            $gameName = $game->name;
            $user = User::findOne($p->user_id);
            $uid = $user->uid;
            $platform = Platform::findOne($platform_id);
            $platformName = $platform->name;

            echo 'out : '.$p->order_id.PHP_EOL;

            $str = $k.','."\t".$p->order_id.','.$uid.','.$gameName.','.$platformName.','.$p->coins.','.$p->money.','."\t".$p->time.PHP_EOL;
            self::saveOrderMatchFile($filename, $str);
        }

        return '';
    }

    //与数据库对比导出csv
    public static function fileMatchDB($file_name, int $order_column, $game_id, $platform_id, $from, $to, $batch)
    {
        $payment = Payment::find()
            ->where(['>=', 'time', $from])
            ->andWhere(['<', 'time', $to])
            ->andWhere(['game_id' => $game_id])
            ->andFilterWhere(['platform_id' => $platform_id]);
        $line = 2;
        do {
            $data = self::getFileLine($file_name, $line);
            var_dump($data);
            if(!boolval($data)){
                continue;
            }

            $e = explode(',', $data);
//            $a = trim($e[0], '"');
//            $a = str_replace("\t", "",$a);
//            $order_id =  $a;
            $order_id =  strtolower(trim($e[$order_column], ' '));
            echo $order_id.PHP_EOL;
            $arr[] = $order_id;

            $line++;
        } while (boolval($data));
        $pArr = [];
        foreach ($payment->each() as $k => $pay) {
            echo $k.PHP_EOL;
            if (!in_array($pay->order_id, $arr)) {
                $hav = self::getOrderMatch($pay, $batch);
                if($hav){
                        echo 'old ID: '.$hav->id.PHP_EOL;
                    continue;
                }

//                $id = self::saveOrderMatch($pay, OrderMatchType::OTHER_LOSE, $batch);
//                echo 'new ID: '.$id.PHP_EOL;
            }
            $pArr[] = $pay->order_id;
        }
//        $diff = array_diff($pArr, $arr);
        $diff = array_diff($arr, $pArr);
        var_dump($diff);
        return true;
    }

    //按批次入库
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

    //从库获取对比订单
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
