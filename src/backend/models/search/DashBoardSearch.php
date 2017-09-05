<?php

namespace backend\models\search;

use common\models\Arrange;
use common\models\OnlineData;
use yii\data\ArrayDataProvider;

class DashBoardSearch extends Arrange
{
    const TWO_DAY = 2;
    const THREE_DAY = 3;
    const MONTH = 1;
    public $from;
    public $to;
    public $type;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'register', 'max_online', 'avg_online', 'pay_money_sum', 'pay_man_sum'], 'integer'],
            [['date', 'created_at', 'to', 'from', 'type', 'game_id'], 'safe'],
        ];
    }

    public function search()
    {
        $provider = new ArrayDataProvider(
            [
                'allModels' => $this->daysData(),
//                'totalCount' => $count,
                'sort' => [
                    'attributes' => [
                    ],
                ],
                'pagination' => [
                    'pageSize' => 10,
                ],
            ]
        );

        return $provider;
    }

    protected function daysData()
    {
        $data = [];
        switch ($this->type) {
            case self::TWO_DAY:
                $this->from = date('Y-m-d', strtotime($this->to.'-1 day'));
                $data[0] = $this->arrangeDay($this->from, $this->from);
                $data[1] = $this->arrangeDay($this->to, $this->to);
                break;
            case self::THREE_DAY:
                $this->from = date('Y-m-d', strtotime($this->to.'-1 month'));
                $data[0] = $this->arrangeDay(date('Y-m-d', strtotime($this->from.'-2 day')), $this->from);
                $data[1] = $this->arrangeDay(date('Y-m-d', strtotime($this->to.'-2 day')), $this->to);
                break;
            case self::MONTH:
                $this->from = date('Y-m-d', strtotime($this->to.'-1 month'));
                $data[0] = $this->arrangeDay(
                    date('Y-m-01', strtotime($this->from)),
                    date('Y-m-d', strtotime($this->from))
                );
                $data[1] = $this->arrangeDay(date('Y-m-01', strtotime($this->to)), date('Y-m-d', strtotime($this->to)));
                break;
        }

        return $data;
    }


    protected function arrangeDay($f, $t)
    {
        $rows = Arrange::getDataByPlatform($f, $t, $this->game_id, '', 'game_id');
        $row = current($rows);
        $output = [
            'new_sum',
            'max_online',
            'pay_money_sum',
            'pay_man_sum',
            'date',
        ];
        foreach (array_flip($output) as $k => $v) {
            $online = OnlineData::getData($f, $this->game_id);
            $output['new_sum'] = $row['new_sum'] ?? '-';
            $output['max_online'] = $online['max_online'] ?? '-';
            $output['avg_online'] = $online['avg_online'] ?? '-';
            $output['pay_money_sum'] = $row['pay_money_sum'] ?? '-';
            $output['pay_man_sum'] = $row['pay_man_sum'] ?? '-';
            $output['date'] = $f == $t ? $f : $f.'/'.$t;
        }

        return $output;
    }
}
