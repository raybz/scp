<?php

namespace console\models;

use common\definitions\UserIsAdult;
use common\models\Game;
use Components\Database\Migration;

/**
 * Class LoginLogTable
 *
 * @property integer $id;
 * @property integer $uid;
 * @property string  $platform;
 * @property string  $gkey;
 * @property integer $gid;
 * @property string  $server_id;
 * @property integer $time;
 * @property integer $is_adult;
 * @property string  $back_url;
 * @property string  $type;
 * @property string  $sign;
 * @property string  $created_at;
 * @package console\models\LoginLogTable
 */
class LoginLogTable extends LogTable
{
    public static $table_prefix = 'login_log_';
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        $month = static::$month ?: date('Ym');
        $month = strtotime($month) >= strtotime(self::MIN_MONTH) ? $month : self::MIN_MONTH;

        return static::$table_prefix.$month;
    }

    public static function column()
    {
        $m = new Migration();
        return [
            'id' => $m->primaryKey(),
            'uid' => $m->string()->notNull(),
            'platform' => $m->string()->notNull()->comment('平台ID'),
            'gkey' => $m->string()->notNull()->comment('游戏名'),
            'gid' => $m->integer()->notNull()->comment('游戏ID'),
            'server_id' => $m->string()->notNull()->comment('区服ID'),
            'time' => $m->dateTime()->notNull()->defaultValue('0000-00-00 00:00:00')->comment('登录时间'),
            'is_adult' => $m->smallInteger(4)->notNull(),
            'back_url' => $m->string()->notNull()->defaultValue('')->comment('登录失败跳转URL'),
            'type' => $m->string()->notNull()->comment('登录类型'),
            'sign' => $m->string()->notNull(),
            'created_at' => $m->dateTime()->notNull(),
        ];
    }

    public static function newTable($date)
    {
        $month = date('Ym', $date);
        $tableName = static::$table_prefix.$month;
        if (!\Yii::$app->log_scp->schema->getTableSchema($tableName)) {
            $migration = new LogMigration();
            $migration->createTable($tableName, self::column());
            $migration->createIndex('login_log_uid_platform_gid_type', $tableName, ['uid', 'platform', 'gid', 'type']);
            $migration->createIndex('login_log_uid_platform_gid_time', $tableName, ['uid', 'platform', 'gid', 'time']);
            $migration->createIndex('login_log_type', $tableName, ['type']);
            $migration->createIndex('login_log_gid_time', $tableName, ['gid', 'time']);
        }
    }

    public static function newData($data)
    {
        $game = Game::getGameByGKey($data->gkey);
        $model = new self();
        $model->uid = $data->uid;
        $model->platform = $data->platform;
        $model->gkey = $data->gkey;
        $model->gid = $game['id'];
        $model->server_id = $data->server_id ?? '';
        $model->time = date('Y-m-d H:i:s', $data->time);
        $model->is_adult = $data->is_adult ?? UserIsAdult::OTHER;
        $model->back_url = $data->back_url ?? '';
        $model->type = $data->type ?? '';
        $model->sign = $data->sign;
        $model->created_at = date('Y-m-d H:i:s');

        if($model->save()) {
            return $model->id;
        } else {
            var_dump($model->errors);
        }

        return null;
    }

    public static function getLogin($uid, $platform, $gid, $time)
    {
        //获取 用户登录 以平台,游戏为准
        $result = self::find()
            ->where('uid = :uid',[':uid' => $uid])
            ->andWhere('platform = :p',[':p' => $platform])
            ->andWhere('gid = :gid',[':gid' => $gid])
            ->andWhere('time = :t',[':t' => $time])
            ->one();

        return $result;
    }

    public static function getUserLoginCount($uid, $platform, $from = null, $to = null)
    {
        $data = self::find()
            ->where('uid = :uid', [':uid' => $uid])
            ->andWhere('platform = :p', [':p' => $platform])
            ->andFilterWhere(['>=', 'time', $from])
            ->andFilterWhere(['<', 'time', $to])
//            ->createCommand()->rawSql;
            ->count();

        return $data ?: 0;
    }

    public static function getUserLatestLogin($uid, $platform, $from = null, $to = null)
    {
        $data = self::find()
            ->where('uid = :uid', [':uid' => $uid])
            ->andWhere('platform = :p', [':p' => $platform])
            ->andFilterWhere(['>=', 'time', $from])
            ->andFilterWhere(['<', 'time', $to])
            ->orderBy('time DESC')
            ->one();

        return $data ?: null;
    }

    public static function storeData($data)
    {
        if (!(isset($data->gkey) && $data->gkey)) {
            return null;
        }
        $game = Game::getGameByGKey($data->gkey);
        if (!$game) {
            return null;
        }
        $time = date('Y-m-d H:i:s', $data->time);
        if (self::getLogin($data->uid, $data->platform, $game->id, $time)) {
            return null;
        }
        $newLogin = self::newData($data);

        return $newLogin;
    }
}