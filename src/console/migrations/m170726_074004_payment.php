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
                'uid' => $this->string()->notNull(),
                'platform_id' => $this->integer()->notNull(),
                'platform' => $this->string()->notNull(),
                'gkey' => $this->string()->notNull(),
                'gid' => $this->integer()->notNull()->comment('游戏ID'),
                'server_id' => $this->string()->notNull(),
                'time' => $this->dateTime()->notNull(),
                'order_id' => $this->string()->notNull(),
                'coins' => $this->integer()->notNull(),
                'money' => $this->float()->notNull(),
                'created_at' => $this->dateTime()->notNull(),
            ]
        );

        $this->createIndex('payment_user_id_pf_id_gid', 'payment', ['user_id', 'platform_id', 'gid']);
        $this->createIndex('payment_uid_pf_oid', 'payment', ['uid', 'platform', 'order_id']);
        $this->createIndex('payment_pf_oid', 'payment', ['platform', 'order_id']);
        $this->createIndex('payment_pf_id_gid', 'payment', ['platform_id', 'gid']);

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
