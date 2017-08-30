<?php

use common\models\MajorLoginHistory;
use kartik\grid\GridView;
use \common\models\Major;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\search\MajorLossSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $platformStr string */
\backend\assets\HighChartsAssets::register($this);
$this->title = '大户流失';
$this->params['breadcrumbs'][] = $this->title;
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
                        'action' => '/major/loss-analysis',
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
                                'MajorLossSearch[platform_id][]',
                                null,
                                [],
                                [
                                    'id' => 'server-payment-search-platform',
                                    'multiple' => true,
                                ]
                            ); ?>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($searchModel, 'date')->widget(
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
    <!--双轴图-->
    <div class="box-header with-border">
        <h3 class="box-title">图表</h3>
        <div class="box-tools pull-right">
            <button class="btn btn-box-tool" data-widget="collapse">
                <i class="fa fa-minus"></i>
            </button>
            <button class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
        </div>
    </div>
    <div class="box-body">
        <div id="loss-analysis-dual-container"></div>
    </div>
</div>
<?php
$columns = [
    ['class' => '\kartik\grid\SerialColumn',],
    [
        'label' => '日期',
        'value' => function ($data) {
            return $data['date'];
        },
        'format' => 'raw',
    ],
    [
        'label' => '大户活跃',
        'value' => function ($data) {
            return $data['active'];
        },
    ],
    [
        'label' => '大户充值',
        'value' => function ($data) {
            return Yii::$app->formatter->asDecimal($data['pMoney'] / 100, 2);
        },
        'format' => 'raw',
    ],
    [
        'label' => '流失数',
        'value' => function ($data) use ($searchModel) {
            $t = date('Y-m-d', strtotime($data['date'].'+1 day'));
            $major = Major::getMajorList($searchModel->game_id, $searchModel->platform_id, '', $t, true);
            $on = MajorLoginHistory::getMajorOnList(
                $searchModel->game_id,
                $searchModel->platform_id,
                $data['date'],
                $t,
                true
            );

            return $major - $on;
        },
    ],
    [
        'label' => '流失率',
        'value' => function ($data) use ($searchModel) {
            $t = date('Y-m-d', strtotime($data['date'].'+1 day'));
            $major = Major::getMajorList($searchModel->game_id, $searchModel->platform_id, '', $t, true);
            $on = MajorLoginHistory::getMajorOnList(
                $searchModel->game_id,
                $searchModel->platform_id,
                $data['date'],
                $t,
                true
            );

            return $major > 0 ? round(($major - $on) / $major * 100, 2) : 0;
        },
    ],
    [
        'label' => '平均生命周期',
        'value' => function ($data) use ($searchModel) {
            $majorIdList = MajorLoginHistory::perDayMajor($data['date']);
            $majorCount = count($majorIdList);

            return $majorCount > 0 ? ceil(MajorLoginHistory::majorTotalLoginCount($majorIdList) / $majorCount) : 0;
        },
    ],
    [
        'label' => '平均贡献LTV',
        'value' => function ($data) use ($searchModel) {
            $t = date('Y-m-d', strtotime($data['date'].'+1 day'));

            return Major::majorLTV($searchModel->game_id, $searchModel->platform_id, '', $t, $data['date']);
        },
    ],
    [
        'label' => '回流大户',
        'value' => function ($data) use ($searchModel) {
            $majorIdList = MajorLoginHistory::perDayMajor($data['date']);

            return Major::majorBackCount($majorIdList);
        },
    ],
];
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
            $columns,
        ],
        'id' => 'server-payment',
        'striped' => false,
        'hover' => false,
        'floatHeader' => false,
        'columns' => $columns,
        'responsive' => true,
        'condensed' => true,
        'panel' => [
            'heading' => '大户列表',
            'type' => 'default',
            'after' => false,
            'before' => false,
        ],
    ]
); ?>
<?php
$chart = <<<EOL
        var param = {
            api: '/api/major-loss-dual?',
            title: {
                text: '',
                align: 'left',
                x: 70
            },
            subtitle:'',
            container: 'loss-analysis-dual-container',
            param: {
                gid: '{$searchModel->game_id}',
                platform: '{$platformStr}',
                from: '{$searchModel->from}',
                to: '{$searchModel->to}',
            }
        };
        var chart = new Hcharts(param);
        chart.showDualAxesLineColumn();
EOL;
$this->registerJs($chart);
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
        original: '#majorlosssearch-game_id',
        aim: '#server-payment-search-platform',
        selected_values_id: '#selected_platform_id',
        url:'/api/get-platform-by-game'
    });
    Component.start();
EOL;

$this->registerJs($script);
?>