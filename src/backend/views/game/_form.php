<?php

use kartik\form\ActiveForm;
use yii\helpers\Html;

/* @var $model \common\models\Game */
?>
<div class="box box-default">
    <div class="box-body">
        <div class="row">
            <style>
                .select2-container .select2-selection--single .select2-selection__rendered {
                    margin-top: 0;
                }
            </style>
            <?php $form = ActiveForm::begin(
                [
                    'successCssClass' => '',
                ]
            ); ?>
            <div class="col-md-12">
                <?= $form->field($model, 'gkey')->textInput() ?>
            </div>
            <div class="col-md-12">
                <?= $form->field($model, 'name')->textInput() ?>
            </div>
            <div class="col-md-12">
                <?= Html::submitButton(
                    $model->isNewRecord ? '新增' : '修改',
                    ['class' => $model->isNewRecord ? 'btn btn-success btn-flat' : 'btn btn-primary btn-flat']
                ) ?>
            </div>
            <?php ActiveForm::end() ?>
        </div>
    </div>
</div>