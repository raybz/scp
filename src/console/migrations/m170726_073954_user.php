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
            'platform' => $this->string()->notNull()->comment('平台'),
            'platform_id' => $this->string()->notNull()->comment('平台ID'),
            'gkey' => $this->string()->notNull()->comment('游戏名'),
            'gid' => $this->integer()->notNull()->comment('游戏ID'),
            'server_id' => $this->string()->notNull()->defaultValue('')->comment('区服ID'),
            'is_adult' => $this->smallInteger(4)->notNull()->defaultValue(0)->comment('实名制/成年'),
            'register_at' => $this->dateTime()->notNull(),
            'status' => $this->smallInteger(6)->notNull()->defaultValue(1),
            'created_at' => $this->dateTime()->notNull(),
        ]);

        $this->createIndex('user_platform_gid', $this->tableName, ['platform', 'gid']);
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
