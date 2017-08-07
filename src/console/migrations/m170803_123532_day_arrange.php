<?php

use Components\Database\Migration;
use yii\helpers\Console;

class m170803_123532_day_arrange extends Migration
{
    protected $tableName = 'day_arrange';
    
    public function up()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'gid' => $this->integer()->notNull()->comment('游戏ID'),
            'date' => $this->date()->notNull()->comment('日期'),
            'register' => $this->integer()->notNull()->defaultValue(0)->comment('注册人数'),
            'max_online' => $this->integer()->notNull()->defaultValue(0)->comment('最高在线'),
            'avg_online' => $this->integer()->notNull()->defaultValue(0)->comment('平均在线'),
            'pay_money_sum' => $this->integer()->notNull()->defaultValue(0)->comment('充值金额'),
            'pay_man_sum' => $this->integer()->notNull()->defaultValue(0)->comment('充值人数'),
            'created_at' => $this->dateTime()->notNull()->defaultValue('0000-00-00 00:00:00'),
        ]);

        $this->addCommentOnTable($this->tableName, '每日汇总表');
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
