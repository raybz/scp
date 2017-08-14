<?php
/* @var $this yii\web\View */
/* @var $model common\models\Game */

$this->title = '新增游戏';
$this->params['breadcrumbs'][] = ['label' => '游戏', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="game-create">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>