<?php

namespace backend\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Major;

/**
 * MajorSearch represents the model behind the search form about `common\models\Major`.
 */
class MajorSearch extends Major
{
    public $from;
    public $to;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'is_adult', 'login_count', 'payment_count', 'total_payment_amount', 'status', 'created_by', 'updated_by'], 'integer'],
            [['register_at', 'platform_id', 'game_id', 'latest_login_at', 'created_at', 'updated_at', 'from', 'to'], 'safe'],
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
        $query = Major::find();

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
            'is_adult' => $this->is_adult,
            'login_count' => $this->login_count,
            'payment_count' => $this->payment_count,
            'total_payment_amount' => $this->total_payment_amount,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_at' => $this->updated_at,
            'updated_by' => $this->updated_by,
        ]);
        $to = isset($this->to) ? $this->to.' 23:59:59' : '';
        $query->andFilterWhere(['platform_id' => $this->platform_id])
            ->andFilterWhere(['game_id' => $this->game_id])
            ->andFilterWhere(['>=', 'register_at', $this->from])
            ->andFilterWhere(['<=', 'latest_login_at', $to]);

        return $dataProvider;
    }
}