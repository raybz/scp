<?php

use kartik\grid\GridView;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\search\ActivitySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '活动列表';
$this->params['breadcrumbs'][] = $this->title;
?>
<style>
    .select2-container .select2-selection--single .select2-selection__rendered{
        margin-top: 0;
    }
</style>
<div class="box box-default">
    <div class="box-body">
        <div class="row">
            <?php $form = \yii\widgets\ActiveForm::begin(
                [
                    'method' => 'get',
                    'action' => '/activity/index',
                ]
            ); ?>

            <div class="col-md-12">
                <div class="col-md-1">
                    <?= $form->field($searchModel, 'game_id')->widget(
                        kartik\select2\Select2::className(),
                        [
                            'data' => \common\models\Game::gameDropDownData(),
                        ]
                    )?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($searchModel, 'range')->widget(
                        \kartik\daterange\DateRangePicker::className(),
                        [
                            'convertFormat' => true,
                            'startAttribute' => 'start_at',
                            'endAttribute' => 'end_at',
                            'pluginOptions' => [
                                'locale' => ['format' => 'Y-m-d'],
                            ],
                        ]
                    )->label('日期:') ?>
                </div>
                <div class="col-md-2">
                    <?= $form->field($searchModel, 'desc')->textInput() ?>
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
<div class="game-index">
    <?php
    $columns = [
        ['class' => 'kartik\grid\SerialColumn'],
        [
            'attribute' => 'game_id',
            'value' => function ($data) {
                $game = \common\models\Game::findOne($data['game_id']);

                return $game->name ?? '';
            },
        ],
        [
            'attribute' => 'name',
        ],
        [
            'attribute' => 'start_at'
        ],
        [
            'attribute' => 'end_at'
        ],
        [
            'attribute' => 'desc',
            'format' => 'raw',
        ],
        [
            'class' => 'kartik\grid\ActionColumn',
            'dropdown' => false,
            'template' => '{update}<span style="margin-left: 10px">{delete}</span>',
        ],
    ];
    $before = Html::a('新增活动', ['create'], ['class' => 'btn btn-success btn-md']);
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
                'heading' => $this->title,
                'type' => 'default',
                'after' => false,
                'before' => Html::a('新增活动', ['activity/create'], ['class' => 'btn btn-success btn-md']),
            ],
        ]
    ); ?>
</div>