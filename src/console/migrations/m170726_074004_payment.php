<?php

use Components\Database\Migration;
use yii\helpers\Console;

class m170726_074004_payment extends Migration
{
    protected $tableName = 'payment';

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
                'created_at' => $this->dateTime()->notNull(),
            ]
        );

        $this->createIndex('payment_gid_pid_sid_uid', 'payment', ['game_id', 'platform_id', 'server_id', 'user_id']);
        $this->createIndex('payment_gid_pid_uid', 'payment', ['game_id', 'platform_id', 'user_id']);
        $this->createIndex('payment_pid_oid_user_id', 'payment', ['platform_id', 'order_id', 'user_id']);

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
