<?php

use kartik\grid\GridView;

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
                            <?php if ($searchModel->platform_id): ?>
                                <input type="hidden" value="<?= join(',', (array)$searchModel->platform_id); ?>"
                                       id="selected_platform_id"/>
                            <?php endif; ?>
                            <?= $form->field($searchModel, 'platform_id')->widget(
                                \dosamigos\multiselect\MultiSelect::className(),
                                [
                                    'data' => \common\models\Platform::platformDropDownData(),
                                    "options" => ['multiple'=>"multiple", "width" => '200px'],
                                    "clientOptions" =>
                                        [
                                            "includeSelectAllOption" => true,
                                            'numberDisplayed' => 2,
                                            'enableFiltering' => true,
                                            'selectAllText' => '全选',
                                            'filterPlaceholder' => '请选择...',
                                            'nonSelectedText' => '未选择',
                                            'buttonWidth' => '100px',
                                        ],
                                ]
                            )->label('平台:') ?>
                        </div>
                    </div>
                    <div class="col-md-1" id="s_l">
                        <div class="form-group">
                            <?php if ($searchModel->server_id): ?>
                                <input type="hidden" value="<?= join(',', (array)$searchModel->server_id); ?>"
                                       id="selected_server_id"/>
                            <?php endif; ?>
                            <?= $form->field($searchModel, 'server_id')->widget(
                                \dosamigos\multiselect\MultiSelect::className(),
                                [
                                    'data' => \common\models\Server::ServerDataDropData(
                                        $searchModel->game_id,
                                        $searchModel->platform_id
                                    ),
                                    "options" => ['multiple'=>"multiple", 'disabled' => 'disabled'],
                                    "clientOptions" =>
                                        [
                                            "includeSelectAllOption" => true,
                                            'enableFiltering' => true,
                                            'numberDisplayed' => 2,
                                            'selectAllText'=> '全选',
                                            'filterPlaceholder' => '请选择...',
                                            'nonSelectedText' => '未选择',
                                            'buttonWidth' => '100px',
                                        ],
                                ]
                            )->label('区服:') ?>
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
            if(Yii::$app->request->get('_type') == 2 && $data['date']) {
                return $data['date'].' / '.date('Y-m-d', strtotime($data['date'].'+1 week'));
            } elseif(Yii::$app->request->get('_type') == 3 && $data['date']) {
                return $data['date'].' / '.date('Y-m-d', strtotime($data['date'].'+1 month'));
            }
            return $data['date'];
        },
        'format' => 'raw',
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
            return round($data['pay_money_sum'], 2);
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
                return round($data['pay_man_sum'] / ($data['active_sum'] + $data['new_sum']) * 100, 2);
            } else {
                return '-';
            }
        },
        'hAlign' => 'center',
    ],
    [
        'label' => 'ARPU',
        'value' => function ($data) {
            if ($data['pay_man_sum'] > 0) {
                return round($data['pay_money_sum'] / $data['pay_man_sum'], 2);
            } else {
                return '-';
            }
        },
        'hAlign' => 'center',
    ],
]; ?>
<?php
$fullExport = \kartik\export\ExportMenu::widget(
    [
        'dataProvider' => $dataProvider,
        'columns' => $columns,
        'fontAwesome' => true,
        'target' => \kartik\export\ExportMenu::TARGET_BLANK,
        'pjaxContainerId' => 'user-behavior-list-grid',
        'asDropdown' => true,
        'showColumnSelector' => false,
        'dropdownOptions' => [
            'label' => '分析概要',
            'class' => 'btn btn-default',
            'itemsBefore' => [
                '<li class="dropdown-header">导出全部数据</li>',
            ],
        ],
    ]
);
?>
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
//            $fullExport,
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
            'footer' => false,
            'before' => false,
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
            <div id="platform-bar-container" <?php
            $pc = count($searchModel->platform_id);
            $total = 34;
            $sin = sin(pi() / 2 * ($pc / $total));
            echo 'style="height: '.(1400 * $sin + 100).'px"' ?>></div>
        </div>
    </div>
<?php
$script = <<<EOL
$('.options').on('click', function () {
    var changeUrlParam = function (name, value) {
        var url = window.location.href;
        var newUrl = "";
        var reg = new RegExp("(^|)" + name + "=([^&]*)(|$)");
        var tmp = name + "=" + value;
        if (url.match(reg) != null) {
            newUrl = url.replace(eval(reg), tmp);
        } else {
            if (url.match("[\?]")) {
                newUrl = url + "&" + tmp;
            }
            else {
                newUrl = url + "?" + tmp;
            }
        }
        return newUrl;
    };

    var _type = $(this).children('input').val();
    $('#_ph').attr('href', changeUrlParam('_type', _type)).click();

    //折线图
    var param1 = {
        api: '/api/user-seep-line?',
        title: {
            text: '付费率',
            align: 'left',
            x: 70
        },
        subtitle: '',
        container: 'per-day-server-bar-container',
        param: {
            gid: '{$searchModel->game_id}',
            platform: '{$platformStr}',
            server: '{$serverStr}',
            from: '{$from}',
            to: '{$to}',
            type: _type
        }
    };
    var chart1 = new Hcharts(param1);
    chart1.showLine();
    //折线图
    var param2 = {
        api: '/api/user-seep-arp-line?',
        title: {
            text: 'ARPU',
            align: 'left',
            x: 70
        },
        subtitle: '',
        container: 'arp-line-container',
        param: {
            gid: '{$searchModel->game_id}',
            platform: '{$platformStr}',
            server: '{$serverStr}',
            from: '{$from}',
            to: '{$to}',
            type: _type
        }
    };
    var chart2 = new Hcharts(param2);
    chart2.showLine();
});
//条形图
var param3 = {
    api: '/api/platform-seep-bar?',
    title: {
        text: '',
        align: 'left',
        x: 70
    },
    subtitle: '',
    container: 'platform-bar-container',
    param: {
        gid: '{$searchModel->game_id}',
        platform: '{$platformStr}',
        server: '{$serverStr}',
        from: '{$from}',
        to: '{$to}',
    }
};
var chart3 = new Hcharts(param3);
chart3.showBar();

$().ready(function () {
    $('#option1').click();
});
EOL;
$this->registerJs($script);
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
        aim: '#userbehaviorsearch-platform_id',
        selected_values_id: '#selected_platform_id',
        url:'/api/get-platform-by-game'
    });
    Component.start();
EOL;

$this->registerJs($script);
$script = <<<EOL
    var Component = new IMultiSelect({
        original: '#userbehaviorsearch-platform_id',
        aim: '#userbehaviorsearch-server_id',
        append: '#s_l', 
        append_show_max_length: 1,
        selected_values_id: '#selected_server_id',
        url:'/api/get-server-by-platform',
        depend:'#userbehaviorsearch-game_id',
    });
    Component.start();
EOL;

$this->registerJs($script);
?>