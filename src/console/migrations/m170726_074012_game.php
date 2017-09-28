<?php

use Components\Database\Migration;
use yii\helpers\Console;

class m170726_074012_game extends Migration
{
    protected $tableName = 'game';
    
    public function up()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'gkey' => $this->string(10)->notNull()->comment('唯一码'),
            'name' => $this->string()->notNull()->comment('游戏名称'),
            'status' => $this->smallInteger(6)->notNull()->defaultValue(1)->comment('状态'),
            'created_at' => $this->dateTime()->notNull(),
            'created_by' => $this->integer()->notNull(),
            'updated_at' => $this->dateTime()->notNull()->defaultValue('0000-00-00 00:00:00'),
            'updated_by' => $this->integer()->notNull()->defaultValue(0),
        ]);
        $this->createIndex('game_gk_s', $this->tableName, ['gkey', 'status']);
        $this->execute("ALTER TABLE ".$this->tableName." AUTO_INCREMENT= 1001");
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
