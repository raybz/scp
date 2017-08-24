<?php

use Components\Database\Migration;
use yii\helpers\Console;

class m170808_083402_arrange extends Migration
{
    protected $tableName = 'arrange';
    
    public function up()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'date' => $this->date()->notNull()->comment('日期'),
            'game_id' => $this->integer()->notNull()->comment('游戏ID'),
            'platform_id' => $this->integer()->notNull()->comment('平台ID'),
            'server_id' => $this->integer()->notNull()->comment('区服ID'),
            'new' => $this->integer()->notNull()->defaultValue(0)->comment('注册人数(新增用户)'),
            'active' => $this->integer()->notNull()->defaultValue(0)->comment('活跃用户'),
            'pay_man' => $this->integer()->notNull()->defaultValue(0)->comment('充值人数'),
            'pay_man_time' => $this->integer()->notNull()->defaultValue(0)->comment('充值人次'),
            'pay_money' => $this->float()->notNull()->defaultValue(0)->comment('充值金额'),
            'new_pay_man' => $this->integer()->notNull()->defaultValue(0)->comment('新增充值人数'),
            'new_pay_money' => $this->float()->notNull()->defaultValue(0)->comment('新增充值金额'),
            'created_at' => $this->dateTime()->notNull()->defaultValue('0000-00-00 00:00:00'),
            'updated_at' => $this->dateTime()->notNull()->defaultValue('0000-00-00 00:00:00'),
        ]);

        $this->createIndex('arrange_date_gid_pid', $this->tableName, ['date', 'game_id', 'platform_id']);
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
