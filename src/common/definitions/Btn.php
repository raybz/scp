<?php
namespace common\definitions;

use Components\BaseDefinition;

class Btn extends BaseDefinition
{
    const INFO = 0;
    const SUCCESS = 1;
    const WARNING = 2;
    const DANGER = 3;

    public static $labels = [
        self::INFO => 'btn btn-xs btn-info',
        self::SUCCESS => 'btn btn-xs btn-success',
        self::WARNING => 'btn btn-xs btn-warning',
        self::DANGER => 'btn btn-xs btn-danger',
    ];
}