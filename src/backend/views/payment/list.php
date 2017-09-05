<?php

use kartik\grid\GridView;

\backend\assets\HighChartsAssets::register($this);
$this->title = '概况';
/* @var $searchModel \backend\models\search\PaymentSearch */
/* @var $gidStr String */
/* @var $platformStr String */
/* @var $total_money String */
/* @var $total_coins String */
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
                        'action' => '/payment/list',
                    ]
                ); ?>

                <div class="col-md-12">
                    <div class="col-md-1">
                        <?= $form->field($searchModel, 'game_id')->widget(
                            \dosamigos\multiselect\MultiSelect::className(),
                            [
                                "options" => ['multiple' => "multiple"],
                                'data' => \common\models\Game::gameDropDownData(),
                                "clientOptions" =>
                                    [
                                        'enableFiltering' => true,
                                        "selectAllText" => '全选',
                                        "includeSelectAllOption" => true,
                                        'numberDisplayed' => false,
                                        'maxHeight' => 0,
                                        'nonSelectedText' => '选择游戏',
                                    ],
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
                                'PaymentSearch[platform_id][]',
                                null,
                                [],
                                [
                                    'id' => 'payment-search-platform',
                                    'multiple' => true,
                                ]
                            ); ?>
                        </div>
                    </div>
                    <div class="col-md-3">
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
        <div class="box-body">
            <div class="row">
                <div class="col-md-2">
                    <label class="control-label">总充值金额：<?= $total_money?></label>
                </div>
                <div class="col-md-2">
                    <label class="control-label">总元宝：<?= $total_coins?></label>
                </div>
            </div>
        </div>
    </div>

<?php $columns = [
    ['class' => '\kartik\grid\SerialColumn', 'pageSummary' => '汇总',],
    [
        'label' => '订单',
        'value' => function ($data) {

            return $data['order_id'] ?? '';
        },
//        'hAlign' => 'center',
    ],
    [
        'label' => '平台',
        'value' => function ($data) {
            $platform = \common\models\Platform::findOne($data['platform_id']);

            return $platform->abbreviation ?? '';
        },
        'hAlign' => 'center',
    ],
    [
        'label' => '游戏',
        'value' => function ($data) {
            $game = \common\models\Game::findOne($data['game_id']);

            return $game->name ?? '';
        },
        'hAlign' => 'center',
    ],
    [
        'label' => '区服',
        'value' => function ($data) {
            $server = \common\models\Server::findOne($data['server_id']);

            return $server->server ?? '';
        },
        'hAlign' => 'center',
    ],
    [
        'label' => '用户',
        'value' => function ($data) {
            $user = \common\models\User::findOne($data['user_id']);

            return $user->uid ?? '';
        },
        'hAlign' => 'center',
    ],
    [
        'label' => '元宝',
        'value' => function ($data) {

            return $data['coins'] ?? 0;
        },
        'hAlign' => 'center',
        'pageSummary' => true,
    ],
    [
        'label' => '金额',
        'value' => function ($data) {
            return $data['money'] ?? 0;
        },
        'hAlign' => 'center',
        'pageSummary' => true,
    ],
    [
        'label' => '下单时间',
        'value' => function ($data) {
            return $data['time'] ?? 0;
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
        'pjaxContainerId' => 'payment-list-grid',
        'asDropdown' => true,
        'showColumnSelector' => false,
        'dropdownOptions' => [
            'label' => '导出数据',
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
            $fullExport,
        ],
        'id' => 'payment-list',
        'striped' => false,
        'hover' => false,
        'floatHeader' => false,
        'columns' => $columns,
        'responsive' => true,
        'condensed' => true,
        'panel' => [
            'heading' => '订单详情',
            'type' => 'default',
            'after' => false,
        ],
    ]
); ?>
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
        original: '#paymentsearch-game_id',
        aim: '#payment-search-platform',
        selected_values_id: '#selected_platform_id',
        url:'/api/get-platform-by-game'
    });
    Component.start();
EOL;

$this->registerJs($script);
?>