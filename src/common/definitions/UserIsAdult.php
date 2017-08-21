<?php
namespace common\definitions;

use Components\BaseDefinition;

class UserIsAdult extends BaseDefinition
{
    const NO_REAL_NAME = 0;
    const REAL_NAME_ADULT = 1;
    const REAL_NAME_NO_ADULT = 2;
    const OTHER = 3;

    public static $labels = [
        self::NO_REAL_NAME => '用户未填写实名制信息',
        self::REAL_NAME_ADULT => '用户填写过实名制信息，且大于18岁',
        self::REAL_NAME_NO_ADULT => '用户填写过实名制信息，但是小于18岁',
        self::OTHER => '丢失',
    ];
}