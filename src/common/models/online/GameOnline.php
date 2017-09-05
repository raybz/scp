<?php
namespace common\models\online;

use Components\Utils\Http;
use yii\db\ActiveRecord;

class GameOnline extends ActiveRecord
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

    public function onLine()
    {
        $this->url = $this->param['url'].'?'.http_build_query($this->query());
        return Http::get($this->url);
    }

    protected function sign()
    {
        return md5($this->gKey.$this->from.$this->to.$this->key);
    }

    public function query()
    {
        return  [
            'gkey' => $this->gKey,
            'from' => $this->from,
            'to' => $this->to,
            'sign' => $this->sign(),
        ];
    }
}
