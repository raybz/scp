<?php

namespace backend\controllers;

use backend\models\search\MajorLossSearch;
use backend\models\search\MajorSearch;
use common\models\Major;
use common\models\Platform;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * MajorController implements the CRUD actions for Major model.
 */
class MajorController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Major models.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new MajorSearch();
        $searchModel->attributes = Yii::$app->request->get('MajorSearch');
        if ($searchModel->from == null || $searchModel->to == null || $searchModel->game_id == null) {
            $searchModel->game_id = 1001;
            $searchModel->from = '2017-03-15';
            $searchModel->to = date('Y-m-d', strtotime('now'));
        }
        if ($searchModel->platform_id == null) {
            $searchModel->platform_id = array_keys(Platform::platformDropDownData());
        }

        $dataProvider = $searchModel->search();

        return $this->render(
            'index',
            [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]
        );
    }

    //大户流失分析
    public function actionLossAnalysis()
    {
        $searchModel = new MajorLossSearch();
        $searchModel->attributes = Yii::$app->request->get('MajorLossSearch');
        if ($searchModel->from == null || $searchModel->to == null || $searchModel->game_id == null) {
            $searchModel->game_id = 1001;
            $searchModel->from = date('Y-m-d', strtotime('-1 month'));
            $searchModel->to = date('Y-m-d', strtotime('now'));
        }
        if ($searchModel->platform_id == null) {
            $searchModel->platform_id = array_keys(Platform::platformDropDownData());
        }
        $platformStr = serialize($searchModel->platform_id);
        $dataProvider = $searchModel->search();

        return $this->render(
            'loss-analysis',
            [
                'platformStr' => $platformStr,
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]
        );
    }

    /**
     * Finds the Major model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Major the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Major::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}