<?php

use Components\Database\Migration;
use yii\helpers\Console;

class m170830_090838_activity extends Migration
{
    protected $tableName = 'activity';
    
    public function up()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'game_id' => $this->integer()->notNull()->comment('游戏ID'),
            'start_at' => $this->date()->notNull()->defaultValue('0000-00-00')->comment('开始时间'),
            'end_at' => $this->date()->notNull()->defaultValue('0000-00-00')->comment('结束时间'),
            'desc' => $this->text()->notNull()->defaultValue('')->comment('说明'),
            'status' => $this->smallInteger(6)->notNull()->defaultValue(1),
            'created_at' => $this->dateTime()->notNull(),
            'created_by' => $this->integer()->notNull(),
            'updated_at' => $this->dateTime()->notNull()->defaultValue('0000-00-00 00:00:00'),
            'updated_by' => $this->integer()->notNull()->defaultValue(0),
        ]);

        $this->addCommentOnTable($this->tableName, '活动表');
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
