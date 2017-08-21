<?php

use Components\Database\Migration;
use yii\helpers\Console;

class m170809_032139_server extends Migration
{
    protected $tableName = 'server';
    
    public function up()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'game_id' => $this->integer()->notNull()->comment('游戏ID'),
            'platform_id' => $this->integer()->notNull()->comment('平台ID'),
            'server' => $this->string()->notNull()->comment('区服'),
            'status' => $this->smallInteger(6)->notNull()->defaultValue(1),
            'created_at' => $this->dateTime()->notNull(),
        ]);

        $this->createIndex('gid_pid_server_index', $this->tableName, ['game_id', 'platform_id','server']);
        $this->addCommentOnTable($this->tableName, '区服表');
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
