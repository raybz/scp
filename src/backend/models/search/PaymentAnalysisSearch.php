<?php

namespace backend\models\search;

use common\models\Arrange;
use common\models\Payment;
use Yii;
use yii\data\ArrayDataProvider;
use yii\data\SqlDataProvider;
use yii\db\Query;

/**
 * ArrangeSearch represents the model behind the search form about `common\models\Arrange`.
 */
class PaymentAnalysisSearch extends Arrange
{
    public $_type;
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
            [['date', 'server_id', 'created_at', 'updated_at', 'from', 'to', 'time', 'go', '_type'], 'safe'],
            [['pay_money', 'new_pay_money'], 'number'],
        ];
    }


    public function search()
    {
        $diff = intval((strtotime($this->to) - strtotime($this->from)) / 86400);
        $diff_m = intval((strtotime($this->to) - strtotime($this->from)) / 3600);
        $rangeTime = range(0, $diff_m);

        if ($diff <= 1) {
            $data = [];
            foreach ($rangeTime as $k => $hour) {
                $f = date('Y-m-d H:i', strtotime($this->from.$hour.' hour'));
                $t = date('Y-m-d H:i', strtotime($this->to.($hour + 1).' hour'));
                $data[] = Payment::getPaymentData(
                    $this->game_id,
                    $this->platform_id,
                    $this->server_id,
                    $f,
                    $t
                );
            }
            $provider = new ArrayDataProvider(
                [
                    'allModels' => $data,
//                'sort' => [
//                    'attributes' => [],
//                ],
                    'pagination' => [
                        'pageSize' => 24,
                    ],
                ]
            );

            return $provider;
        }
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
            ->andFilterWhere(['server_id' => $this->server_id])
            ->groupBy('date')
            ->orderBy('date DESC');

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