<?php

/* @var $this yii\web\View */
/* @var $model common\models\Activity */

$this->title = '更新活动: '.$model->name;
$this->params['breadcrumbs'][] = ['label' => 'Activities', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="activity-update">

    <?= $this->render(
        '_form',
        [
            'model' => $model,
        ]
    ) ?>

</div>