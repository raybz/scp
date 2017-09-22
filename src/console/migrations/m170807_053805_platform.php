<?php

use Components\Database\Migration;
use yii\helpers\Console;

class m170807_053805_platform extends Migration
{
    protected $tableName = 'platform';
    
    public function up()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull()->defaultValue('')->comment('平台名称'),
            'abbreviation' => $this->string()->notNull()->defaultValue('')->comment('平台英文缩写'),
            'status' => $this->smallInteger(6)->notNull()->defaultValue(1),
            'created_at' => $this->dateTime()->notNull()->defaultValue('0000-00-00 00:00:00'),
            'updated_at' => $this->dateTime()->notNull()->defaultValue('0000-00-00 00:00:00'),
            'updated_by' => $this->integer()->notNull()->defaultValue(0),
        ]);

        $this->createIndex('platform_abbreviation_status', $this->tableName, ['abbreviation', 'status']);
        $this->addCommentOnTable($this->tableName, '平台表');
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
