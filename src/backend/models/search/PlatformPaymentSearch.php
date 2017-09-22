<?php

namespace backend\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Arrange;
use yii\data\SqlDataProvider;
use yii\db\Query;

/**
 * ArrangeSearch represents the model behind the search form about `common\models\Arrange`.
 */
class PlatformPaymentSearch extends Arrange
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
        $query = (new Query())->from('arrange')
            ->select([
                'date',
                'game_id',
                'platform_id',
                'server_id',
                'sum(new) new_sum',
                'sum(active) active_sum',
                'sum(pay_man) pay_man_sum',
                'sum(pay_money) pay_money_sum',
                'sum(new_pay_man) new_pay_man_sum',
                'sum(new_pay_money) new_pay_money_sum',
            ])
            ->where('date >= :from AND date < :to',
                [
                    ':from' => $this->from,
                    ':to' => $this->to
                ])
            ->andFilterWhere(['game_id' => $this->game_id])
            ->andFilterWhere(['platform_id' => $this->platform_id])
            ->groupBy('platform_id');

        list($sql, $sqlParams) = Yii::$app->db->getQueryBuilder()->build($query);
        $count = count($query->column());
        $dataProvider = new SqlDataProvider([
            'sql' => $sql,
            'params' => $sqlParams,
            'totalCount' => $count,
            'sort' => [
                'attributes' => [
                    'pay_money_sum',
                    'new_sum',
                    'active_sum',
                    'pay_man_sum',
                    'new_pay_man_sum',
                    'new_pay_money_sum',
                ],
                'defaultOrder' => [
//                    'pay_money_sum DESC'
                ],
            ],
            'pagination' => [
                'pageSize' => 50
            ]
        ]);

        return $dataProvider;
    }
}