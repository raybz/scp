<?php

use Components\Database\Migration;
use yii\helpers\Console;

class m170912_015250_payment_add_column_flag extends Migration
{
    protected $tableName = 'payment';
    
    public function up()
    {
        $this->addColumn($this->tableName, 'flag', 'integer  after money');
        $this->addCommentOnColumn($this->tableName, 'flag', '订单状态');
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
