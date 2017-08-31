<?php

use kartik\grid\GridView;
use \kartik\widgets\DateTimePicker;

\backend\assets\HighChartsAssets::register($this);
$this->title = '概况';
/* @var $searchModel \backend\models\search\PaymentAnalysisSearch */
/* @var $platformStr string */
/* @var $serverStr string */
?>
    <style>
        .select2-container .select2-selection--single .select2-selection__rendered {
            margin-top: 0;
        }
    </style>
    <div class="box box-default">
        <div class="box-body">
            <div class="row">
                <?php $form = \yii\widgets\ActiveForm::begin(
                    [
                        'method' => 'get',
                        'action' => '/user-behavior/habit',
                    ]
                ); ?>

                <div class="col-md-12">
                    <div class="col-md-1">
                        <?= $form->field($searchModel, 'game_id')->widget(
                            kartik\select2\Select2::className(),
                            [
                                'data' => \common\models\Game::gameDropDownData(),
                            ]
                        )->label('游戏:') ?>
                    </div>
                    <div class="col-md-1">
                        <div class="form-group">
                            <label class="control-label">平台:</label>
                            <?php if ($searchModel->platform_id): ?>
                                <input type="hidden" value="<?= join(',', (array)$searchModel->platform_id); ?>"
                                       id="selected_platform_id"/>
                            <?php endif; ?>
                            <?= \yii\helpers\Html::dropDownList(
                                'UserBehaviorSearch[platform_id][]',
                                null,
                                [],
                                [
                                    'id' => 'server-payment-search-platform',
                                    'multiple' => true,
                                ]
                            ); ?>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="form-group">
                            <label class="control-label">区服:</label>
                            <?php if ($searchModel->server_id): ?>
                                <input type="hidden" value="<?= join(',', (array)$searchModel->server_id); ?>"
                                       id="selected_server_id"/>
                            <?php endif; ?>
                            <?= \yii\helpers\Html::dropDownList(
                                'UserBehaviorSearch[server_id][]',
                                null,
                                [],
                                [
                                    'id' => 'server-payment-search-server',
                                    'multiple' => true,
                                ]
                            ); ?>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <?= $form->field($searchModel, 'time')->widget(
                            \kartik\daterange\DateRangePicker::className(),
                            [
                                'convertFormat' => true,
                                'startAttribute' => 'from',
                                'endAttribute' => 'to',
                                'pluginOptions' => [
                                    'locale' => ['format' => 'Y-m-d'],
                                ],
                            ]
                        )->label('日期:') ?>
                    </div>
                    <div class="col-md-1">
                        <?= \yii\helpers\Html::submitButton(
                            '搜索',
                            ['class' => 'btn btn-success btn-flat', 'style' => 'margin-top: 25px;']
                        ) ?>
                    </div>
                </div>
                <?php \yii\widgets\ActiveForm::end() ?>
            </div>
        </div>
    </div>

    <div class="box box-default">
        <!--折线图-->
        <div class="box-header with-border">
            <h3 class="box-title">充值频次</h3>
            <div class="box-tools pull-right">
                <button class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-minus"></i>
                </button>
                <button class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
            </div>
        </div>
        <div class="box-body">
            <div id="freq-bar-container" ></div>
        </div>
    </div>
    <div class="box box-default">
        <!--条形图-->
        <div class="box-header with-border">
            <h3 class="box-title">充值额度</h3>
            <div class="box-tools pull-right">
                <button class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-minus"></i>
                </button>
                <button class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
            </div>
        </div>
        <div class="box-body">
            <div id="quota-bar-container" style="height: 560px;"></div>
        </div>
    </div>
    <div class="box box-default">
        <!--条形图-->
        <div class="box-header with-border">
            <h3 class="box-title">充值间隔</h3>
            <div class="box-tools pull-right">
                <button class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-minus"></i>
                </button>
                <button class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
            </div>
        </div>
        <div class="box-body">
            <div id="gap-bar-container" style="height: 560px;"></div>
        </div>
    </div>
<?php
$charts = <<<EOL
    //条形图-频率
    var param = {
        api: '/api/user-habit-pay-freq-bar?',
        title: {
            text: '',
            align: 'left',
            x: 70
        },
        subtitle:'',
        container: 'freq-bar-container',
        param: {
            gid: '{$searchModel->game_id}',
            platform: '{$platformStr}',
            server:'{$serverStr}',
            from: '{$searchModel->from}',
            to: '{$to}',
        }
    };
    var chart = new Hcharts(param);
    chart.showBar();
    
    //条形图-额度
    param = {
        api: '/api/user-habit-pay-quota-bar?',
        title: {
            text: '',
            align: 'left',
            x: 70
        },
        subtitle:'',
        container: 'quota-bar-container',
        param: {
            gid: '{$searchModel->game_id}',
            platform: '{$platformStr}',
            server:'{$serverStr}',
            from: '{$searchModel->from}',
            to: '{$to}',
        }
    };
    chart = new Hcharts(param);
    chart.showBar();
    
    //条形图-间隔
    param = {
        api: '/api/user-habit-pay-gap-bar?',
        title: {
            text: '',
            align: 'left',
            x: 70
        },
        subtitle:'',
        container: 'gap-bar-container',
        param: {
            gid: '{$searchModel->game_id}',
            platform: '{$platformStr}',
            server:'{$serverStr}',
            from: '{$searchModel->from}',
            to: '{$to}',
        }
    };
    chart = new Hcharts(param);
    chart.showBar();
EOL;
$this->registerJs($charts);
?>
<?php
$this->registerJsFile(
    '/js/linkage_multi.js',
    [
        'depends' => [
            'backend\assets\MultiSelectFilterAsset',
        ],
    ]
);
$script = <<<EOL
    var Component = new IMultiSelect({
        original: '#userbehaviorsearch-game_id',
        aim: '#server-payment-search-platform',
        selected_values_id: '#selected_platform_id',
        url:'/api/get-platform-by-game'
    });
    Component.start();
EOL;

$this->registerJs($script);
$script1 = <<<EOL
    var Component = new IMultiSelect({
        original: '#selected_platform_id',
        aim: '#server-payment-search-server',
        selected_values_id: '#selected_server_id',
        url:'/api/get-server-by-platform',
        depend:'#userbehaviorsearch-game_id',
    });
    Component.start();
EOL;

$this->registerJs($script1);
?>