<?php

use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Activity */
/* @var $form yii\widgets\ActiveForm */
$this->title = '活动详情: '.$model->name;
$this->params['breadcrumbs'][] = ['label' => 'Activities', 'url' => ['index']];
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
            <div class="activity-form">

                <?php $form = ActiveForm::begin(); ?>
                <div class="col-md-12">
                    <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'disabled' => true]) ?>
                </div>

                <div class="col-md-12">
                    <?= $form->field($model, 'game_id')->widget(
                        kartik\select2\Select2::className(),
                        [
                            'data' => \common\models\Game::gameDropDownData(),
                            'disabled' => true,
                        ]
                    ) ?>
                </div>
                <?php
                $layout = <<< HTML
    <span class="input-group-addon">开始时间</span>
    {input1}
    <span class="input-group-addon">aft</span>
    {separator}
    <span class="input-group-addon">结束时间</span>
    {input2}
    <span class="input-group-addon kv-date-remove">
        <i class="glyphicon glyphicon-remove"></i>
    </span>
HTML;
                ?>
                <div class="col-md-12">
                    <label class="control-label" for="activity-date">日期</label>
                    <?= \kartik\date\DatePicker::widget(
                        [
                            'type' => \kartik\date\DatePicker::TYPE_RANGE,
                            'model' => $model,
                            'attribute' => 'start_at',
                            'attribute2' => 'end_at',
                            'convertFormat' => true,
                            'separator' => '<i class="glyphicon glyphicon-resize-horizontal"></i>',
                            'layout' => $layout,
                            'pluginOptions' => [
                                'autoclose' => true,
                                'format' => 'yyyy-M-dd',
                                'todayHighlight' => true,
                                'disabled' => true,
                            ],
                        ]
                    ); ?>
                </div>
                <div class="col-md-12" style="margin-top: 16px;">
                    <?= $form->field($model, 'desc')->textarea(['rows' => 6, 'disabled' => true]) ?>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>