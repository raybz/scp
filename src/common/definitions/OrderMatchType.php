<?php
namespace common\definitions;

use Components\BaseDefinition;

class OrderMatchType extends BaseDefinition
{
    const ALL_HAD = 1;
    const WE_LOSE = 2;
    const OTHER_LOSE = 3;

    public static $labels = [
        self::ALL_HAD => '双方都有',
        self::WE_LOSE => '我方没有',
        self::OTHER_LOSE => '对方没有',
    ];
}