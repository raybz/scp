<?php

use Components\Database\Migration;
use yii\helpers\Console;

class m170819_074428_user_game_server_relation extends Migration
{
    protected $tableName = 'user_game_server_relation';
    
    public function up()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'game_id' => $this->integer()->notNull(),
            'server_id' => $this->integer()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ]);

        $this->createIndex('uid_gid_sid_index', $this->tableName, ['user_id', 'game_id', 'server_id']);
        $this->addCommentOnTable($this->tableName, '用户游戏区服关联表');
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
