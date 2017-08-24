<?php

namespace backend\models\search;

use common\models\Arrange;
use common\models\Payment;
use Yii;
use yii\base\Object;
use yii\data\ArrayDataProvider;
use yii\data\SqlDataProvider;
use yii\db\ActiveRecord;
use yii\db\Query;

/**
 * ArrangeSearch represents the model behind the search form about `common\models\Arrange`.
 */
class ServerPaymentSearch extends Arrange
{
    public $from;
    public $go;
    public $to;
    public $time;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'game_id', 'platform_id', 'new', 'active', 'pay_man', 'new_pay_man'], 'integer'],
            [['date', 'server_id', 'created_at', 'updated_at', 'from', 'to', 'time', 'go'], 'safe'],
            [['pay_money', 'new_pay_money'], 'number'],
        ];
    }


    public function search()
    {
        /* @var $query  */
        $query = Arrange::getDataByServer($this->from, $this->to, $this->game_id, $this->platform_id, $this->server_id, 'platform_id,server_id', '', '', false);

        list($sql, $sqlParams) = Yii::$app->db->getQueryBuilder()->build($query);
        $count = count($query->column());
        $dataProvider = new SqlDataProvider([
            'sql' => $sql,
            'params' => $sqlParams,
            'totalCount' => $count,
            'sort' => [
                'attributes' => [
                ],
                'defaultOrder' => [
                ],
            ],
            'pagination' => [
                'pageSize' => 50
            ]
        ]);

        return $dataProvider;
    }
}