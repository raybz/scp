<?php

use Components\Database\Migration;
use yii\helpers\Console;

class m170822_114812_order_match extends Migration
{
    protected $tableName = 'order_match';

    public function up()
    {
        $this->createTable(
            $this->tableName,
            [
                'id' => $this->primaryKey(),
                'user_id' => $this->integer()->notNull(),
                'game_id' => $this->integer()->notNull()->comment('游戏ID'),
                'platform_id' => $this->integer()->notNull(),
                'server_id' => $this->integer()->notNull(),
                'time' => $this->dateTime()->notNull(),
                'order_id' => $this->string()->notNull(),
                'coins' => $this->integer()->notNull(),
                'money' => $this->float()->notNull(),
                'type' => $this->integer()->notNull()->defaultValue(1),
                'created_at' => $this->dateTime()->notNull(),
                'batch' => $this->integer()->notNull()->comment('批次'),
            ]
        );

        $this->createIndex('p_gid_pid_sid_uid', $this->tableName, ['game_id', 'platform_id', 'server_id', 'user_id']);
        $this->createIndex('p_gid_pid_uid', $this->tableName, ['game_id', 'platform_id', 'user_id']);
        $this->createIndex('p_pid_oid_user_id', $this->tableName, ['platform_id', 'order_id', 'user_id']);
        $this->createIndex('p_pid_oid_batch', $this->tableName, ['platform_id', 'order_id', 'batch']);

        $this->addCommentOnTable($this->tableName, '充值表');
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
