<?php

namespace backend\models\search;

use common\models\Payment;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * PaymentSearch represents the model behind the search form about `common\models\Payment`.
 */
class PaymentSearch extends Payment
{
    public $from;
    public $to;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'server_id', 'coins'], 'integer'],
            [['time', 'order_id', 'created_at', 'from', 'to', 'game_id', 'platform_id'], 'safe'],
            [['money'], 'number'],
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
        $query = Payment::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider(
            [
                'query' => $query,
                'sort' => [
                    'defaultOrder' => [
                        'time' => 'DESC'
                    ]
                ]
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
                'user_id' => $this->user_id,
                'game_id' => $this->game_id,
                'platform_id' => $this->platform_id,
                'server_id' => $this->server_id,
                'coins' => $this->coins,
                'money' => $this->money,
                'created_at' => $this->created_at,
            ]
        );
        $to = isset($this->to) ? $this->to.' 23:59:59' : '';
        $query->andFilterWhere(['>=', 'time', $this->from])
            ->andFilterWhere(['<=', 'time', $to]);

        $query->andFilterWhere(['like', 'order_id', $this->order_id]);

        return $dataProvider;
    }
}