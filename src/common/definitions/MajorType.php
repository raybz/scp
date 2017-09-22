<?php
namespace common\definitions;

use Components\BaseDefinition;

class MajorType extends BaseDefinition
{
    const NEW = 2;
    const ACTIVE = 3;
    const LOSS = 4;
    const BACK = 5;

    public static $labels = [
        self::NEW => '新进',
        self::ACTIVE => '活跃',
        self::LOSS => '流失',
        self::BACK => '回流',
    ];
}
