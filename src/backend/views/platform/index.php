<?php

use kartik\dynagrid\DynaGrid;
use kartik\grid\GridView;
use kartik\editable\Editable;
/* @var $this yii\web\View */
/* @var $searchModel backend\models\search\PlatformSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '平台';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="game-index">
    <?php
    $columns = [
        ['class'=>'kartik\grid\SerialColumn'],
        [
            'attribute' => 'name',
            'label' => '名称',
            'class' => 'kartik\grid\EditableColumn',
            'editableOptions' => [
                'size' => \kartik\popover\PopoverX::SIZE_MEDIUM,
                'format' => Editable::FORMAT_LINK,
            ],
        ],
        [
            'attribute' => 'abbreviation',
            'label' => '平台',
        ],
        [
            'attribute' => 'status',
            'class'=>'kartik\grid\BooleanColumn',
            'vAlign'=>'middle',
            'width' => '10%',
        ],
    ];
    ?>
    <?= DynaGrid::widget(
        [
            'options' => ['id' => 'game-1'],
            'columns' => $columns,
            'storage' => DynaGrid::TYPE_COOKIE,
            'theme' => 'panel-danger',
            'gridOptions' => [
                'toolbar' => [],
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'panel' => [
                    'type' => GridView::TYPE_INFO,
                    'heading' => '<h3 class="panel-title">平台列表</h3>',
                    'toolbar' => [],
                    'before' => false,
                    'after' => false,
                ],
            ],
        ]
    )?>
</div>