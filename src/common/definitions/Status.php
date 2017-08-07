<?php
namespace common\definitions;

use Components\BaseDefinition;

class Status extends BaseDefinition
{
    const ACTIVE = 1;
    const DELETE = 0;

    public static $labels = [
      self::ACTIVE => '正常',
      self::DELETE => '删除',
    ];
}
