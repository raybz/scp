<?php

namespace backend\models\search;

use common\models\MajorLoginHistory;
use common\models\User;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Major;
use yii\db\Query;
use yii\helpers\Html;

/**
 * MajorSearch represents the model behind the search form about `common\models\Major`.
 */
class MajorLossSearch extends MajorLoginHistory
{
    public $from;
    public $to;
    public $platform_id;
    public $game_id;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['date', 'latest_login_at', 'from', 'to', 'platform_id', 'game_id'], 'safe'],
            [['major_id', 'money'], 'required'],
            [['major_id', 'pay_times', 'login_count'], 'integer'],
            [['money'], 'number'],
        ];
    }

    public function search()
    {
        $query = (new Query())->from('major_login_history h')
            ->select([
                'date',
                'COUNT(*) active',
                'SUM(money) pMoney',
            ])
            ->leftJoin('major m', 'h.major_id = m.id')
            ->groupBy('date')
            ->orderBy('date DESC');

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $to = isset($this->to) ? $this->to.' 23:59:59' : '';
        $query->andFilterWhere(['platform_id' => $this->platform_id])
            ->andFilterWhere(['game_id' => $this->game_id])
            ->andFilterWhere(['>=', 'date', $this->from])
            ->andFilterWhere(['<=', 'date', $to]);

        return $dataProvider;
    }
}