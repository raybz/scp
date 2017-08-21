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
            'gid' => $this->integer()->notNull()->comment('游戏ID'),
            'platform_id' => $this->string()->notNull()->comment('平台ID'),
            'server_id' => $this->integer()->notNull()->defaultValue(0)->comment('区服ID'),
            'is_adult' => $this->smallInteger(4)->notNull()->defaultValue(0)->comment('实名制/成年'),
            'register_at' => $this->dateTime()->notNull()->comment("注册时间"),
            'last_login_at' => $this->dateTime()->notNull()->comment("最后登录时间"),
            'status' => $this->smallInteger(6)->notNull()->defaultValue(1),
            'created_at' => $this->dateTime()->notNull()->comment("新进时间"),
            'created_by' => $this->integer()->notNull()->defaultValue(0),
            'updated_at' => $this->dateTime()->notNull()->defaultValue('0000-00-00 00:00:00'),
            'updated_by' => $this->integer()->notNull()->defaultValue(0),
        ]);

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
