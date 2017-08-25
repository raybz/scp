<?php

use kartik\grid\GridView;
use \kartik\widgets\DateTimePicker;

\backend\assets\HighChartsAssets::register($this);
$this->title = '概况';
/* @var $searchModel \backend\models\search\ServerPaymentSearch */
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
                        'action' => '/user-behavior/seep',
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
                                'PaymentAnalysisSearch[platform_id][]',
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
                                'ServerPaymentSearch[server_id][]',
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
            <div class="btn-group" data-toggle="buttons" id="_type">
                <label class="btn btn-primary active options" id="option1">
                    <input type="radio" name="options"  autocomplete="off" checked value="1"> 按日
                </label>
                <label class="btn btn-primary options">
                    <input type="radio" name="options" id="option2" autocomplete="off" value="2"> 按周
                </label>
                <label class="btn btn-primary options">
                    <input type="radio" name="options" id="option3" autocomplete="off" value="3"> 按月
                </label>
            </div>
            <div class="box-tools pull-right">
                <button class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-minus"></i>
                </button>
                <button class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
            </div>
        </div>
        <div class="box-body">
            <div id="per-day-server-bar-container" ></div>
        </div>
    </div>
    <div class="box box-default">
        <!--折线图-->
        <div class="box-header with-border">
            <h3 class="box-title">ARPU</h3>
            <div class="box-tools pull-right">
                <button class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-minus"></i>
                </button>
                <button class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
            </div>
        </div>
        <div class="box-body">
            <div id="arp-line-container"></div>
        </div>
    </div>

<?php $columns = [
    ['class' => '\kartik\grid\SerialColumn', 'pageSummary' => '汇总'],
    [
        'label' => '时间',
        'hAlign' => 'center',
        'value' => function ($data) {
            return $data['date'];
        },
    ],
    [
        'label' => '活跃用户',
        'hAlign' => 'center',
        'value' => function ($data) {
            return $data['active_sum'] + $data['new_sum'];
        },
        'pageSummary' => true,
    ],
    [
        'label' => '充值金额',
        'value' => function ($data) {
            return Yii::$app->formatter->asDecimal($data['pay_money_sum'], 2);
        },
        'hAlign' => 'center',
        'pageSummary' => true,
    ],
    [
        'label' => '充值人数',
        'hAlign' => 'center',
        'value' => function ($data) {
            return $data['pay_man_sum'];
        },
        'pageSummary' => true,
    ],
    [
        'label' => '付费渗透率(%)',
        'value' => function ($data) {
            if ($data['active_sum'] > 0) {
                return Yii::$app->formatter->asDecimal($data['pay_man_sum'] / ($data['active_sum'] + $data['new_sum']) * 100);
            } else {
                return '-';
            }
        },
        'hAlign' => 'center',
    ],
    [
        'label' => 'ARPU(%)',
        'value' => function ($data) {
            if ($data['pay_man_sum'] > 0) {
                return Yii::$app->formatter->asDecimal($data['pay_money_sum'] / $data['pay_man_sum'] * 100);
            } else {
                return '-';
            }
        },
        'hAlign' => 'center',
    ],
]; ?>
<?= GridView::widget(
    [
        'autoXlFormat' => true,
        'showPageSummary' => true,
        'pageSummaryRowOptions' => ['class' => 'kv-page-summary default'],
        'export' => [
            'fontAwesome' => true,
            'showConfirmAlert' => false,
            'target' => GridView::TARGET_BLANK,
        ],
        'dataProvider' => $dataProvider,
        'pjax' => true,
        'toolbar' => [
            $columns,
        ],
        'id' => 'payment-game',
        'striped' => false,
        'hover' => false,
        'floatHeader' => false,
        'columns' => $columns,
        'responsive' => true,
        'condensed' => true,
        'panel' => [
            'heading' => \yii\helpers\Html::a('', ['/user-behavior/seep'],['id' => '_ph']),
            'type' => 'default',
            'after' => false,
            'before' => false,
            'footer' => false,
        ],
    ]
); ?>
    <div class="box box-default">
        <!--条形图-->
        <div class="box-header with-border">
            <h3 class="box-title">各平台付费渗透</h3>
            <div class="box-tools pull-right">
                <button class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-minus"></i>
                </button>
                <button class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
            </div>
        </div>
        <div class="box-body">
            <div id="platform-bar-container" style="height: 560px;"></div>
        </div>
    </div>
<?php
$charts = <<<EOL
    $('.options').on('click', function(){
        var _type = $(this).children('input').val();
        $('#_ph').attr('href', '/user-behavior/seep?_type='+_type).click();    
        
    //折线图
        var param = {
            api: '/api/user-seep-line?',
            title: {
                text: '付费率',
                align: 'left',
                x: 70
            },
            subtitle:'',
            container: 'per-day-server-bar-container',
            param: {
                gid: '{$searchModel->game_id}',
                platform: '{$platformStr}',
                server:'{$serverStr}',
                from: '{$from}',
                to: '{$to}',
                type: _type
            }
        };
        var chart = new Hcharts(param);
        chart.showLine();
    //折线图
        var param = {
            api: '/api/user-seep-arp-line?',
            title: {
                text: 'ARPU',
                align: 'left',
                x: 70
            },
            subtitle:'',
            container: 'arp-line-container',
            param: {
                gid: '{$searchModel->game_id}',
                platform: '{$platformStr}',
                server:'{$serverStr}',
                from: '{$from}',
                to: '{$to}',
                type: _type
            }
        };
        var chart = new Hcharts(param);
        chart.showLine();
    });
    //条形图
    var param = {
        api: '/api/platform-seep-bar?',
        title: {
            text: '',
            align: 'left',
            x: 70
        },
        subtitle:'',
        container: 'platform-bar-container',
        param: {
            gid: '{$searchModel->game_id}',
            platform: '{$platformStr}',
            server:'{$serverStr}',
            from: '{$from}',
            to: '{$to}',
        }
    };
    var chart = new Hcharts(param);
    chart.showBar();
    
    $().ready(function(){
        $('#option1').click();
    });
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
        original: '#serverpaymentsearch-game_id',
        aim: '#server-payment-search-platform',
        selected_values_id: '#selected_platform_id',
        url:'/api/get-platform-by-game'
    });
    Component.start();
EOL;

$this->registerJs($script);
$script = <<<EOL
    var Component = new IMultiSelect({
        original: '#selected_platform_id',
        aim: '#server-payment-search-server',
        selected_values_id: '#selected_server_id',
        url:'/api/get-server-by-platform',
        depend:'#serverpaymentsearch-game_id',
    });
    Component.start();
EOL;

$this->registerJs($script);
?>