<?php
namespace common\definitions;

use Components\BaseDefinition;

class PayStatus extends BaseDefinition
{
    const SUCCESS = 0;
    const REPEAT = 1;
    const LOSS_PARAM = -1;
    const SIGN_ERROR = -2;
    const USER_NO_EXIST = -3;
    const REQUEST_TIMEOUT = -4;

    public static $labels = [
        self::SUCCESS => '充值成功',
        self::REPEAT => '订单重复',
        self::LOSS_PARAM => '参数不全',
        self::SIGN_ERROR => '签名错误',
        self::USER_NO_EXIST => '用户不存在',
        self::REQUEST_TIMEOUT => '请求超时',
    ];
}