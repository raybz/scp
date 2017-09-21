<?php

namespace common\models\api;

use Components\Utils\Http;
use yii\console\Exception;
use yii\db\ActiveRecord;
use yii\helpers\StringHelper;

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
        $name = $this->formatClassName();
        $param = \Yii::$app->params['paramGame'];
        $this->param = $param[$this->gKey][$name];
        $this->key = $this->param['key'];
    }
    //类名必须包含 game
    //类名中game后的字符 为配置参数索引
    private final function formatClassName()
    {
        $className = StringHelper::basename(get_class($this));
        if (!stripos($className, 'game')){
            throw new Exception('className is not right; please format like eg: **Game** '.PHP_EOL);
        }

        return substr(strrchr(strtolower($className), 'game'),4);
    }

    public function cUrl($index = 0)
    {
        if (is_array($this->param['url'])) {
            $domain = $this->param['url'][$index] ?? '';
        } else {
            $domain = $this->param['url'];
        }
        $this->url = $domain.'?'.http_build_query($this->query());
        echo $this->url.PHP_EOL;

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

