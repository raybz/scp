<?php

namespace backend\models\search;

use common\models\Major;
use common\models\User;
use yii\data\ActiveDataProvider;
use yii\db\Query;

/**
 * MajorSearch represents the model behind the search form about `common\models\Major`.
 */
class MajorSearch extends Major
{
    public $from;
    public $to;
    public $uid;
    public $latest_login_at ;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'is_adult', 'payment_count', 'total_payment_amount', 'status', 'created_by', 'updated_by'], 'integer'],
            [['register_at', 'platform_id', 'game_id', 'latest_login_at', 'created_at', 'updated_at', 'from', 'to', 'uid'], 'safe'],
        ];
    }

    public function search()
    {
        $query = (new Query())->from('major m')
            ->select('m.*, h.latest_login_at')
            ->leftJoin('major_login_history h', 'm.id = h.major_id')
            ->orderBy('h.latest_login_at DESC');

        $q = (new Query())->from(['q' => $query])->groupBy('q.user_id')->orderBy('q.latest_login_at DESC');

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider(
            [
                'query' => $q,
                'sort' => [
                    'attributes' => [
                        'latest_login_at',
                    ],
                ],
            ]
        );

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'is_adult' => $this->is_adult,
//            'login_count' => $this->login_count,
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
            ->andFilterWhere(['user_id' => User::getUserDetail($this->uid, $this->platform_id) ?? ''])
            ->andFilterWhere(['>=', 'register_at', $this->from])
            ->andFilterWhere(['<=', 'latest_login_at', $to]);

        return $dataProvider;
    }
}