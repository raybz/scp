<?php

use Components\Database\Migration;
use yii\helpers\Console;

class m170726_073954_user extends Migration
{
    protected $tableName = 'user';
    
    public function up()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'uid' => $this->string()->notNull(),
            'platform_id' => $this->string()->notNull()->comment('平台ID'),
            'is_adult' => $this->smallInteger(4)->notNull()->defaultValue(0)->comment('实名制/成年'),
            'register_at' => $this->dateTime()->notNull(),
            'status' => $this->smallInteger(6)->notNull()->defaultValue(1),
            'created_at' => $this->dateTime()->notNull(),
        ]);

        $this->createIndex('user_pid', $this->tableName, ['platform_id', 'uid']);
        $this->createIndex('user_uid', $this->tableName, ['uid']);
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
