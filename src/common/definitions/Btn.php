<?php
namespace common\definitions;

use Components\BaseDefinition;

class Btn extends BaseDefinition
{
    const SUCCESS = 1;
    const WARNING = 2;
    const DANGER = 3;

    public static $labels = [
        self::SUCCESS => 'btn btn-success',
        self::WARNING => 'btn btn-warning',
        self::DANGER => 'btn btn-danger',
    ];
}