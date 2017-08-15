<?php

use Components\Database\Migration;
use yii\helpers\Console;

class m170815_014237_add_game_platform_server_index extends Migration
{
    protected $tableName = 'game_platform_server';
    
    public function up()
    {
        $this->createIndex('gid_pf_id_server_id', $this->tableName, ['game_id', 'platform_id', 'server_id']);
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
