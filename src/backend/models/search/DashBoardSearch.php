<?php

namespace backend\models\search;

use common\models\DayArrange;
use yii\data\ArrayDataProvider;

/**
 * DayArrangeSearch represents the model behind the search form about `common\models\DayArrange`.
 */
class DashBoardSearch extends DayArrange
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
            [['id','register', 'max_online', 'avg_online', 'pay_money_sum', 'pay_man_sum'], 'integer'],
            [['date', 'created_at', 'to', 'from', 'type', 'gid'], 'safe'],
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
        switch ($this->type){
            case self::TWO_DAY:
                $this->from = date('Y-m-d', strtotime($this->to.'-1 day'));
                $data[0] = $this->arrangeDay($this->from, $this->from);
                $data[1] = $this->arrangeDay($this->to, $this->to);
            break;
            case self::THREE_DAY:
                $this->from = date('Y-m-d', strtotime($this->to.'-1 month'));
                $data[0] = $this->arrangeDay(date('Y-m-d', strtotime($this->from.'-2 day')),$this->from);
                $data[1] = $this->arrangeDay(date('Y-m-d', strtotime($this->to.'-2 day')), $this->to);
                break;
            case self::MONTH:
                $this->from = date('Y-m-d', strtotime($this->to.'-1 month'));
                $data[0] = $this->arrangeDay(date('Y-m-01', strtotime($this->from)), date('Y-m-d', strtotime($this->from)));
                $data[1] = $this->arrangeDay(date('Y-m-01', strtotime($this->to)), date('Y-m-d', strtotime($this->to)));
            break;
        }
        return $data;
    }


    protected function arrangeDay($f, $t)
    {
        $attr = $this->attributes;
        unset($attr['id'], $attr['date'], $attr['created_at']);
        $attr = array_keys($attr);
        $row = [];
        foreach ($attr as $v){
            $row['date'] = $f == $t ? $f : $f.'/'.$t;
            $row[$v] = DayArrange::getColumnSum($v, $f, $t, $this->gid);
        }

        return $row;
    }
}
