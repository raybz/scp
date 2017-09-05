<?php

use Components\Database\Migration;
use yii\helpers\Console;

class m170904_012524_online_data extends Migration
{
    protected $tableName = 'online_data';
    
    public function up()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'date' => $this->date()->notNull(),
            'game_id' => $this->integer()->notNull(),
            'avg_online' => $this->float()->notNull()->defaultValue(0)->comment('平均在线'),
            'max_online' => $this->float()->notNull()->defaultValue(0)->comment('最高在线'),
            'created_at' => $this->dateTime()->notNull(),
        ]);

        $this->createIndex('ol_date_gid', $this->tableName, ['date', 'game_id']);
        $this->addCommentOnTable($this->tableName, '屠龙战纪在线表');
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
