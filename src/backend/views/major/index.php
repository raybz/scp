<?php

use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\search\MajorSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '大户列表';
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
                    'action' => '/major/index',
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
                            'MajorSearch[platform_id][]',
                            null,
                            [],
                            [
                                'id' => 'major-search-platform',
                                'multiple' => true,
                            ]
                        ); ?>
                    </div>
                </div>
                <div class="col-md-3">
                    <?= $form->field($searchModel, 'register_at')->widget(
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
                <div class="col-md-2">
                    <?= $form->field($searchModel, 'uid')->textInput()->label('平台帐号:')?>
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
<?php
$columns = [
    ['class' => '\kartik\grid\SerialColumn',],
    [
        'label' => '帐号',
        'value' => function ($data) {
            $user = \common\models\User::findOne($data['user_id']);

            return $user->uid ?? '';
        },
        'format' => 'raw',
    ],
    [
        'label' => '平台',
        'value' => function ($data) {
            $platform = \common\models\Platform::findOne($data['platform_id']);

            return $platform->abbreviation ?? '';
        },
    ],
    [
        'label' => '充值总额',
        'attribute' => 'total_payment_amount',
        'value' => function ($data) {
            return Yii::$app->formatter->asDecimal($data['total_payment_amount'] / 100, 2);
        },
        'format' => 'raw',
    ],
    [
        'label' => '今日充值',
        'value' => function ($data) {
            $from = date('Y-m-d');
            $to = date('Y-m-d', strtotime('tomorrow'));

            return \common\models\Payment::getPerTimeMoney(
                $data['game_id'],
                $from,
                $to,
                $data['user_id'],
                $data['platform_id']
            );
        },
    ],
    [
        'label' => '30日充值',
        'value' => function ($data) {
            $from = date('Y-m-d', strtotime('-30 day'));
            $to = date('Y-m-d', strtotime('tomorrow'));

            return \common\models\Payment::getPerTimeMoney(
                $data['game_id'],
                $from,
                $to,
                $data['user_id'],
                $data['platform_id']
            );
        },
    ],
    [
        'label' => '新进时间',
        'attribute' => 'created_at',
    ],
    [
        'label' => '注册时间',
        'attribute' => 'register_at',
    ],
    [
        'label' => '最后登录时间',
        'attribute' => 'latest_login_at',
        'value' => function ($data) {
            $now = strtotime(date('Y-m-d'));
            $last = strtotime(date('Y-m-d', strtotime($data['latest_login_at'])));
            $diff = ($now - $last) / 86400;
            $btn = $diff > 3 ? 3 : $diff;

            return \yii\helpers\Html::button($data['latest_login_at'], ['class' => \common\definitions\Btn::getLabel($btn)]).' '.
                \yii\helpers\Html::button($diff, ['class' => \common\definitions\Btn::getLabel($btn)]);
        },
        'format' => 'raw',
        'hAlign' => GridView::ALIGN_CENTER,
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
            $fullExport,
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
        original: '#majorsearch-game_id',
        aim: '#major-search-platform',
        selected_values_id: '#selected_platform_id',
        url:'/api/get-platform-by-game'
    });
    Component.start();
EOL;

$this->registerJs($script);
?>