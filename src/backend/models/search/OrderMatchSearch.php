<?php

namespace backend\models\search;

use common\models\OrderMatch;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * OrderMatchSearch represents the model behind the search form about `common\models\OrderMatch`.
 */
class OrderMatchSearch extends OrderMatch
{
    public $from;
    public $to;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'game_id', 'platform_id', 'server_id', 'coins', 'type', 'batch'], 'integer'],
            [['time', 'order_id', 'created_at', 'from', 'to'], 'safe'],
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
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = OrderMatch::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'user_id' => $this->user_id,
            'game_id' => $this->game_id,
            'platform_id' => $this->platform_id,
            'server_id' => $this->server_id,
            'time' => $this->time,
            'coins' => $this->coins,
            'money' => $this->money,
            'type' => $this->type,
            'created_at' => $this->created_at,
            'batch' => $this->batch,
        ]);

        $to = isset($this->to) ? $this->to.' 23:59:59' : '';
        $query
            ->andFilterWhere(['>=', 'time', $this->from])
            ->andFilterWhere(['<', 'time', $to]);
        $query->andFilterWhere(['like', 'order_id', $this->order_id]);

        return $dataProvider;
    }
}