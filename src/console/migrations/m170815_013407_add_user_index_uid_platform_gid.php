<?php

use Components\Database\Migration;
use yii\helpers\Console;

class m170815_013407_add_user_index_uid_platform_gid extends Migration
{
    protected $tableName = 'user';
    
    public function up()
    {
        $this->createIndex('user_uid_platform_gid', $this->tableName, ['uid', 'platform', 'gid']);
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
