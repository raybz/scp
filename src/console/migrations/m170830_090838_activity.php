<?php

use Components\Database\Migration;
use yii\helpers\Console;

class m170830_090838_activity extends Migration
{
    protected $tableName = '{{%table}}';
    
    public function up()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'game_id' => $this->integer()->notNull()->comment('游戏'),
            'created_at' => $this->dateTime()->notNull(),
            'created_by' => $this->integer()->notNull(),
            'updated_at' => $this->dateTime()->notNull()->defaultValue('0000-00-00 00:00:00'),
            'updated_by' => $this->integer()->notNull()->defaultValue(0),
        ]);

        $this->addCommentOnTable($this->tableName, '表');
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
