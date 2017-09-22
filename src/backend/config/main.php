<?php
$params = array_merge(
    require(__DIR__.'/../../common/config/params.php'),
    require(__DIR__.'/../../common/config/params-local.php'),
    require(__DIR__.'/params.php'),
    require(__DIR__.'/params-local.php')
);

return [
    'id' => 'scp.2144.cn',
    'name' => 'SCP后台管理',
    'basePath' => dirname(__DIR__),
    'defaultRoute' => 'site/index',
    'language' => 'zh-CN',
    'timezone' => 'Asia/Shanghai',
    'controllerNamespace' => 'backend\controllers',
    'bootstrap' => ['log'],
    'modules' => [
        "admin" => [
            "class" => \mdm\admin\Module::class,
        ],
        'gridview' => [
            'class' => '\kartik\grid\Module',
        ],
        'dynagrid' => [
            'class' => 'kartik\dynagrid\Module',
        ]
    ],
    'as access' => [
        'class' => \mdm\admin\components\AccessControl::class,
        'allowActions' => [
            'site/login',
            'site/error',
            'site/logout',
            'site/idlogin',
            'site/idregister',
            'api/*',
        ],
    ],
    'components' => [
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
            "defaultRoles" => ["guest"],
        ],

        'audit' => [
            'class' => \Components\Audit::class,
            'systemId' => 45,
            'authKey' => 'lD0uOX26tCAIEPPlAiwBkgrjx7Dc8tsw',
        ],
        'idauth' => [
            'class' => \Components\IdAuth::class,
            'systemId' => 45,
            'authKey' => 'lD0uOX26tCAIEPPlAiwBkgrjx7Dc8tsw',
        ],
        'request' => [
            'csrfParam' => '_csrf-backend-scp',
        ],
        'urlManager' => [
            'class' => 'yii\web\UrlManager',
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            /** urlRules */
            'rules' => require __DIR__.'/urlRules.php',
        ],
        'user' => [
            'identityClass' => 'backend\models\Admin',
            'enableAutoLogin' => true,
            'loginUrl' => ['site/login'],
            'identityCookie' => ['name' => '_identity-backend', 'httpOnly' => true],
        ],
        'session' => [
            'class' => \yii\redis\Session::class,
        ],
        /**
         * debug开启日志
         */
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
    ],
    'params' => $params,
];
