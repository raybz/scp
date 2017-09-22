<?php

namespace backend\assets;

use yii\web\AssetBundle;

/**
 * Main backend application asset bundle.
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/site.css',
//        'https://cdn.bootcss.com/font-awesome/4.7.0/css/font-awesome.css'
    ];
    public $js = [
        'js/main.js'
    ];
    public $depends = [
//
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
//        'rmrevin\yii\fontawesome\AssetBundle',
    ];
}
