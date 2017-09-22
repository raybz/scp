<?php

use Components\Database\Migration;
use yii\helpers\Console;

class m170825_100711_major_login_history extends Migration
{
    protected $tableName = 'major_login_history';
    
    public function up()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'date' => $this->date()->notNull()->defaultValue('0000-00-00'),
            'major_id' => $this->integer()->notNull()->comment('大户ID'),
            'money' => $this->float()->notNull()->comment('支付金额'),
            'pay_times' => $this->integer()->defaultValue(0)->comment('支付次数'),
            'latest_login_at' => $this->dateTime()->notNull()->defaultValue('0000-00-00 00:00:00')->comment('当日最后登录时间'),
            'login_count' => $this->integer()->notNull()->defaultValue(0)->comment('当日登录次数'),
        ]);

        $this->createIndex('major_date_mid', $this->tableName, ['date', 'major_id']);
        $this->addCommentOnTable($this->tableName, '大户登录记录表');
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
