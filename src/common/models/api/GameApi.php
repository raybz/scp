<?php

namespace common\models\api;

use Components\Utils\Http;
use yii\db\ActiveRecord;

class GameApi extends ActiveRecord
{
    public $from;
    public $to;

    protected $gKey = 'tlzj';
    protected $url;
    protected $param;
    protected $key;

    public function init()
    {
        $param = \Yii::$app->params['paramGame'];
        $this->param = $param[$this->gKey];
        $this->key = $this->param['key'];
    }

    public function cUrl($index = 0)
    {
        if (is_array($this->param['url'])) {
            $domain = $this->param['url'][$index] ?? '';
        } else {
            $domain = $this->param['url'];
        }
        $this->url = $domain.'?'.http_build_query($this->query());

        return Http::get($this->url);
    }

    public function query()
    {
        return [
            'gkey' => $this->gKey,
            'from' => $this->from,
            'to' => $this->to,
            'sign' => $this->sign(),
        ];
    }

    protected function sign()
    {
        return md5($this->gKey.$this->from.$this->to.$this->key);
    }
}

