<?php

namespace backend\models\search;

use common\models\Arrange;
use Yii;
use yii\data\ArrayDataProvider;
use yii\data\SqlDataProvider;
use yii\db\Query;

/**
 * ArrangeSearch represents the model behind the search form about `common\models\Arrange`.
 */
class UserBehaviorSearch extends Arrange
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
        if ($this->_type > 1) {
            $arr = [];
            $zeroDateTime = (new \DateTime())->setTimestamp(0);
            $valueDateTime = (new \DateTime())->setTimestamp(abs(strtotime($this->to)-strtotime($this->from)));
            $interval = $valueDateTime->diff($zeroDateTime);

            if ($this->_type == 2){
                $diff = intval((strtotime($this->to) - strtotime($this->from)) / (86400 * 7));
                $limit = ' week';
            } else {
                $diff = $interval->m + ($interval->d > 0 ? 1 : 0);
                $limit = ' month';
            }

            $rangeTime = range(0, $diff);


            foreach ($rangeTime as $k => $day) {
                $f = date('Y-m-d', strtotime($this->from.$day.$limit));
                $t = date('Y-m-d', strtotime($this->from.($day + 1).$limit));
                $result = current(
                    Arrange::getDataByServer($f, $t, $this->game_id, $this->platform_id, $this->server_id)
                );
                $arr[] = $result;
            }

            $dataProvider = new ArrayDataProvider(
                [
                    'allModels' => $arr,
                ]
            );

            return $dataProvider;
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