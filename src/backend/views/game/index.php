<?php

use kartik\dynagrid\DynaGrid;
use kartik\grid\GridView;
use yii\helpers\Html;
/* @var $this yii\web\View */
/* @var $searchModel backend\models\search\GameSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '游戏';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="game-index">
    <?php
        $columns = [
            ['class'=>'kartik\grid\SerialColumn'],
            ['attribute' => 'id'],
            ['attribute' => 'gkey'],
            [
                'attribute' => 'name',
                'label' => '游戏',
            ],
            [
                'attribute' => 'status',
                'class'=>'kartik\grid\BooleanColumn',
                'vAlign'=>'middle',
                'width' => '10%',
            ],
            [
                'class'=>'kartik\grid\ActionColumn',
                'dropdown'=> false,
                'template' => '{update}<span style="margin-left: 10px">{delete}</span>'
            ],
        ];
        $before = Html::a('新增游戏', ['create'], ['class' => 'btn btn-success btn-md']);
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
                    'heading' => '<h3 class="panel-title">游戏列表</h3>',
                    'toolbar' => [],
                    'before' => $before,
                    'after' => false,
                ],
            ],
        ]
    )?>
</div>