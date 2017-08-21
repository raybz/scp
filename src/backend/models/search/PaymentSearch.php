<?php

namespace backend\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Payment;

/**
 * PaymentSearch represents the model behind the search form about `common\models\Payment`.
 */
class PaymentSearch extends Payment
{
    public $gid;
    public $from;
    public $to;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'coins'], 'integer'],
            [['game_id', 'platform_id', 'server_id', 'time', 'order_id', 'created_at', 'from', 'to', 'gid'], 'safe'],
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
        $query = Payment::find();

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
            'coins' => $this->coins,
            'money' => $this->money,
            'created_at' => $this->created_at,
        ]);

        $query->andFilterWhere(['platform'=> $this->platform_id])
            ->andFilterWhere(['server_id'=> $this->server_id])
            ->andFilterWhere(['like', 'time', $this->time])
            ->andFilterWhere(['like', 'order_id', $this->order_id]);

        return $dataProvider;
    }
}