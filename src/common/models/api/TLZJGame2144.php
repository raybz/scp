<?php
namespace common\models\api;

/**
 * Class TLZJGame2144
 * @property array login
 * @property array order
 * @package common\models\api
 */
class TLZJGame2144 extends GameApi
{
    public function init()
    {
        $this->gKey = 'tulong';
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
