<?php
namespace console\models;

use yii\db\Migration;

class LogMigration extends Migration
{
    public function init()
    {
        $this->db = \Yii::$app->get('log_scp');
        parent::init();
    }
}