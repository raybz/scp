<?php

use kartik\grid\GridView;

\backend\assets\HighChartsAssets::register($this);
$this->title = '概况';
/* @var $searchModel \backend\models\search\DashBoardSearch*/
/* @var $threeDayDataProvider \backend\models\search\DashBoardSearch*/
/* @var $monthDataProvider \backend\models\search\DashBoardSearch*/
?>
    <style>
        .select2-container .select2-selection--single .select2-selection__rendered{
            margin-top: 0;
        }
    </style>
<div class="box box-default">
    <!--折线图-->
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
        <div id="per-hour-money-container"></div>
    </div>
</div>

<div class="box box-default">
    <!--折线图-->
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
        <div id="per-hour-man-container"></div>
    </div>
</div>

<div class="box box-default">
    <div class="box-body">
        <div class="row">
            <?php $form = \yii\widgets\ActiveForm::begin(
                [
                    'method' => 'get',
                    'action' => '/common/dashboard',
                ]
            ); ?>

            <div class="col-md-12">

                <div class="col-md-1">
                    <?= $form->field($searchModel, 'game_id')->widget(\kartik\select2\Select2::className(),[
                        'data' => \common\models\Game::gameDropDownData(),
                        'options' => ['placeholder' => '请选择游戏'],
                    ])->label('游戏:') ?>
                </div>
                <div class="col-md-1">
                    <?= $form->field($searchModel, 'to')->widget(\kartik\date\DatePicker::className(),[
                        'value' => '',
                        'options' => ['placeholder' => '日期'],
                        'type' => \kartik\date\DatePicker::TYPE_INPUT,
                        'pluginOptions' => [
                            'format' => 'yyyy-mm-dd',
                            'autoclose' => true,
                        ],
                    ])->label('日期:') ?>
                </div>
                <div class="col-md-1">
                    <?= \yii\helpers\Html::submitButton('搜索', ['class' => 'btn btn-success btn-flat', 'style' => 'margin-top: 25px;'])?>
                </div>
            </div>
            <?php \yii\widgets\ActiveForm::end()?>
        </div>
    </div>
</div>
<?php $columns = [
    [
        'attribute' => 'date',
        'hAlign' => 'center',
        'label' => '日期',
        'width' => '16%',
        'pageSummary' => '增幅'
    ],
    [
        'attribute' => 'new_sum',
        'hAlign' => 'center',
        'label' => '注册',
        'pageSummary' => function ($data, $key) {
            if (isset($key[0]) && isset($key[1]) && $key[0] > 0) {
                $diff = intval($key[1]) - intval($key[0]);
                $MoM = ($diff / $key[0]) * 100;

                return Yii::$app->formatter->asDecimal($MoM);
            } else {
                return '-';
            }
        },
        'format' => 'raw',
    ],
    [
        'attribute' => 'max_online',
        'hAlign' => 'center',
        'label' => '最高在线',
        'pageSummary' => function ($data, $key) {
            if (isset($key[0]) && isset($key[1]) && $key[0] > 0) {
                $diff = $key[1] - $key[0];
                $MoM = ($diff / $key[0]) * 100;

                return Yii::$app->formatter->asDecimal($MoM);
            } else {
                return '-';
            }
        },
        'format' => 'raw',
    ],
    [
        'attribute' => 'avg_online',
        'hAlign' => 'center',
        'label' => '平均在线',
        'pageSummary' => function ($data, $key) {
            if (isset($key[0]) && isset($key[1]) && $key[0] > 0) {
                $diff = $key[1] - $key[0];
                $MoM = ($diff / $key[0]) * 100;

                return Yii::$app->formatter->asDecimal($MoM);
            } else {
                return '-';
            }
        },
        'format' => 'raw',
    ],
    [
        'attribute' => 'pay_money_sum',
        'hAlign' => 'center',
        'label' => '充值金额',
        'value' => function($data){
            if(is_numeric($data['pay_money_sum'])){
                return Yii::$app->formatter->asDecimal($data['pay_money_sum']);
            }
            return $data['pay_money_sum'];
        },
        'pageSummary' => function ($data, $key) {
            if (isset($key[0]) && isset($key[1]) && $key[0] > 0) {
                $diff = intval($key[1]) - intval($key[0]);
                $MoM = $diff / intval($key[0]) * 100;

                return Yii::$app->formatter->asDecimal($MoM);
            } else {
                return '-';
            }
        },
        'format' => 'raw',
    ],
    [
        'attribute' => 'pay_man_sum',
        'hAlign' => 'center',
        'label' => '充值人数',
        'pageSummary' => function ($data, $key) {
            if (isset($key[0]) && isset($key[1]) && $key[0] > 0) {
                $diff = $key[1] - $key[0];
                $MoM = ($diff / $key[0]) * 100;

                return Yii::$app->formatter->asDecimal($MoM);
            } else {
                return '-';
            }
        },
        'format' => 'raw',
    ],
];?>
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
        'id' => 'dashboard',
        'striped' => false,
        'hover' => false,
        'floatHeader' => false,
        'columns' => $columns,
        'responsive' => true,
        'condensed' => true,
        'panel' => [
            'heading' => '两日数据对比',
            'type' => 'default',
            'after' => false,
            'before' => false,
        ],
    ]
); ?>
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
        'dataProvider' => $threeDayDataProvider,
        'pjax' => true,
        'toolbar' => [
//            $fullExport,
        ],
        'id' => 'dashboard-three-day',
        'striped' => false,
        'hover' => false,
        'floatHeader' => false,
        'columns' => $columns,
        'responsive' => true,
        'condensed' => true,
        'panel' => [
            'heading' => '三日数据对比',
            'type' => 'default',
            'after' => false,
            'before' => false,
        ],
    ]
); ?>
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
        'dataProvider' => $monthDataProvider,
        'pjax' => true,
        'toolbar' => [
//            $fullExport,
        ],
        'id' => 'dashboard-one-month',
        'striped' => false,
        'hover' => false,
        'floatHeader' => false,
        'columns' => $columns,
        'responsive' => true,
        'condensed' => true,
        'panel' => [
            'heading' => '月数据对比',
            'type' => 'default',
            'after' => false,
            'before' => false,
        ],
    ]
); ?>
<!--actionPlatformDateCharts-->
<?php
//每时/充值走势图
$pay_charts = <<<EOL
        var param = {
            api: '/api/today-payment-spline?',
            title: {
                text: '今日充值金额：',
                align: 'left',
                x: 70
            },
            subtitle:'',
            container: 'per-hour-money-container',
        };
        var chart = new Hcharts(param);
        chart.showSpline();
EOL;
$this->registerJs($pay_charts);
//每时/充值人数走势图
$charts = <<<EOL
        var param = {
            api: '/api/today-payment-spline?type=money',
            title: {
                text: '今日充值人数：',
                align: 'left',
                x: 70
            },
            subtitle:'',
            container: 'per-hour-man-container',
        };
        var chart = new Hcharts(param);
        chart.showSpline();
EOL;
$this->registerJs($charts);
?>