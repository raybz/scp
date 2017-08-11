<?php

use Components\Database\Migration;
use yii\helpers\Console;

class m170809_032139_game_platform_server extends Migration
{
    protected $tableName = 'game_platform_server';
    
    public function up()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'game_id' => $this->integer()->notNull()->comment('游戏ID'),
            'platform_id' => $this->integer()->notNull()->comment('平台ID'),
            'server_id' => $this->integer()->notNull()->comment('区服ID'),
            'status' => $this->smallInteger(6)->notNull()->defaultValue(1),
            'created_at' => $this->dateTime()->notNull(),
        ]);

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
