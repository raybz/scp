<?php
namespace console\models;

use Components\Database\Migration;
use yii\db\ActiveRecord;

/**
 * Class LogTable
 *
 * @property integer $id;
 * @property string  $host;
 * @property string  $time;
 * @property string  $stamp;
 * @property string  $request_method;
 * @property string  $url;
 * @property string  $status_code;
 * @property string  $sent_bytes;
 * @property string  $referrer;
 * @property string  $user_agent;
 * @property string  $xff;
 * @property string  $post_data;
 * @property string  $url_hash;
 * @package console\models\LogTable
 */
class LogTable extends ActiveRecord
{
    const MIN_MONTH = '201608';
    public static $table_prefix = "log_";
    public static $month = '';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        $month = static::$month ?: date('Ym');
        $month = strtotime($month) >= strtotime(self::MIN_MONTH) ? $month : self::MIN_MONTH;

        return static::$table_prefix.$month;
    }

    public function rules()
    {
        return parent::rules();
    }

    public static function getDb()
    {
        return \Yii::$app->get('log_scp');
    }

    public static function column()
    {
        $m = new Migration();
        return [
            'id' => $m->primaryKey(),
            'host' => $m->string()->notNull()->defaultValue('0.0.0.0'),
            'time' => $m->dateTime()->notNull()->defaultValue('0000-00-00 00:00:00'),
            'stamp' => $m->integer(10)->notNull(),
            'request_method' => $m->string()->notNull(),
            'url' => $m->text()->notNull(),
            'status_code' => $m->smallInteger()->notNull(),
            'sent_bytes' => $m->integer()->notNull()->defaultValue(0),
            'referrer' => $m->string()->notNull()->defaultValue(''),
            'user_agent' => $m->string()->notNull()->defaultValue(''),
            'post_data' => $m->string()->notNull()->defaultValue(''),
            'xff' => $m->string()->notNull()->defaultValue(''),
            'url_hash' => $m->char(40)->notNull()->defaultValue(''),
        ];
    }

    public function saveData($data)
    {
        $model = new self;
        $model->host = $data->host;
        $model->time = date('Y-m-d H:i:s', strtotime($data->time));
        $model->stamp = $data->stamp;
        $model->request_method = $data->requestMethod;
        $model->url = $data->URL;
        $model->status_code = $data->status;
        $model->sent_bytes = $data->sentBytes;
        $model->referrer = $data->HeaderReferer;
        $model->user_agent = $data->HeaderUserAgent;
        $model->xff = $data->XFF;
        $model->post_data = $data->postData ?? '';
        $model->url_hash = hash('sha1', $data->URL.$data->stamp);

        if($model->save()) {
            return $model->id;
        } else{
            var_dump($model->errors);exit;
        }

        return null;
    }

    public static function getNextMonth($months, $i = 0)
    {
        $year = (explode('-', $months))[0];
        $month = (explode('-', $months))[1];
        if (($month + $i) < 13) {
            $month = $month + $i;
        } else {
            $year = $year + 1;
            $month = $month + $i - 12;
        }

        return $year.sprintf("%02d", $month);
    }

    public static function getMonths($from, $to)
    {
        $date1 = explode('-', $from);
        $date2 = explode('-', $to);
        $months = ($date2[0] - $date1[0]) * 12 + abs($date1[1] - $date2[1]);

        return $months;
    }

    public static function logTableMonth($from, $to)
    {
        $months = self::getMonths($from, $to);
        $monthArr = [];
        for ($i = 0; $i <= $months; $i++) {
            $month = self::getNextMonth($from, $i);
            $tableName = static::$table_prefix.$month;
            if (!\Yii::$app->log_scp->schema->getTableSchema($tableName)) {
                continue;
            }
            $monthArr[] = $month;
        }

        return $monthArr;
    }

    //筛选有效天
    public static function getDiffDay($from, $to)
    {
        $diff = (strtotime($to) - strtotime($from)) / 86400;
//        var_dump($diff);
        $monthArr = self::logTableMonth($from, $to);
        $v_to = [];
        if ($diff > 1) {
            if (in_array(date('Ym', strtotime($from)), $monthArr)) {
                $v_to[] = $from;
            }

            for ($i = 0; $i <= ceil($diff); $i++) {
                $stamp = strtotime($to.(-floor($diff).' day').($i.' day'));

                $tableDate = date('Ym', $stamp);

                if (in_array($tableDate, $monthArr)){
                    $v_to[] = date('Y-m-d H:i:s', $stamp);
                }
            }
        }

        return $v_to;
    }

    public static function newTable($date)
    {
        $month = date('Ym', $date);
        $tableName = static::$table_prefix.$month;
        if (!\Yii::$app->log_scp->schema->getTableSchema($tableName)) {
            $migration = new LogMigration();

            $migration->createTable($tableName, self::column());
            $migration->createIndex('stamp_url_hash_index', $tableName, ['stamp', 'url_hash']);
        }
    }

    public function findUnique($data)
    {
        $result = self::find()
            ->where(['stamp' => $data->stamp])
            ->andWhere(['url_hash' => hash('sha1', $data->URL.$data->stamp)])
            ->one();

        return $result;
    }
}