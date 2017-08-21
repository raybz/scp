<?php

use Components\Database\Migration;
use yii\helpers\Console;

class m170818_021236_major extends Migration
{
    protected $tableName = 'major';
    
    public function up()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'game_id' => $this->integer()->notNull()->comment('游戏ID'),
            'platform_id' => $this->string()->notNull()->comment('平台ID'),
            'is_adult' => $this->smallInteger(4)->notNull()->defaultValue(3)->comment('实名制/成年'),
            'register_at' => $this->dateTime()->notNull()->comment("注册时间"),
            'latest_login_at' => $this->dateTime()->notNull()->comment("最后登录时间"),
            'login_count' => $this->integer()->notNull()->defaultValue(0)->comment("累计登录次数"),
            'payment_count' => $this->integer()->notNull()->defaultValue(0)->comment("累计充值笔数"),
            'total_payment_amount' => $this->integer()->notNull()->defaultValue(0)->comment("累计充值金额: 单位为：分"),
            'status' => $this->smallInteger(6)->notNull()->defaultValue(1),
            'created_at' => $this->dateTime()->notNull()->comment("新进时间"),
            'created_by' => $this->integer()->notNull()->defaultValue(0),
            'updated_at' => $this->dateTime()->notNull()->defaultValue('0000-00-00 00:00:00'),
            'updated_by' => $this->integer()->notNull()->defaultValue(0),
        ]);

        $this->createIndex('uid_gid_pid', $this->tableName, ['user_id', 'game_id', 'platform_id']);
        $this->addCommentOnTable($this->tableName, '表');
    }
    
    public function down()
    {
        $confirm = Console::confirm("Do you want drop tables:{$this->tableName}?");
        if ($confirm) {
            $this->dropTable($this->tableName);
            Console::output('Drop tables done!');
        } else {
            Console::output('Canceled!');
        }
    }
}
