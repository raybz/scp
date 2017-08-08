<?php

namespace backend\models\search;

use common\models\DayArrange;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * DayArrangeSearch represents the model behind the search form about `common\models\DayArrange`.
 */
class DayArrangeSearch extends DayArrange
{
    public $from;
    public $to;
    public $platform;
    public $time;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                ['id', 'register', 'active', 'max_online', 'avg_online', 'pay_man_sum', 'register_pay_man_sum'],
                'integer',
            ],
            [['date', 'created_at', 'from', 'to','platform', 'time', 'gid'], 'safe'],
            [['pay_money_sum', 'register_pay_money_sum'], 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = DayArrange::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider(
            [
                'query' => $query,
            ]
        );

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere(
            [
                'id' => $this->id,
                'gid' => $this->gid,
                'register' => $this->register,
                'active' => $this->active,
                'max_online' => $this->max_online,
                'avg_online' => $this->avg_online,
                'pay_man_sum' => $this->pay_man_sum,
                'pay_money_sum' => $this->pay_money_sum,
                'register_pay_man_sum' => $this->register_pay_man_sum,
                'register_pay_money_sum' => $this->register_pay_money_sum,
                'created_at' => $this->created_at,
            ]
        );

        $query->andFilterWhere(['>=', 'date', $this->from])
            ->andFilterWhere(['<', 'date', $this->to]);

        return $dataProvider;
    }
}