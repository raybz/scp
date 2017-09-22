<?php
namespace common\models\api;

/**
 * Class TLZJGame2144
 * @property array login
 * @property array order
 * @package common\models\api
 */
class TLZJGame6255 extends GameApi
{
    public function init()
    {
        $this->gKey = 'tlzjx';
        parent::init();
    }

    public function getLogin()
    {
        return json_decode($this->cUrl(0));
    }

    public function getOrder()
    {
        return json_decode($this->cUrl(1));
    }
}
