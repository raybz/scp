<?php

namespace backend\controllers;

use backend\models\search\DayArrangeSearch;
use common\models\Game;
use Yii;
use common\models\Payment;
use backend\models\search\PaymentSearch;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * PaymentController implements the CRUD actions for Payment model.
 */
class PaymentController extends Controller
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
     * Lists all Payment models.
     * @return mixed
     */
    public function actionIndex()
    {
//        $searchModel = new PaymentSearch();
        $searchModel = new DayArrangeSearch();
        $get = Yii::$app->request->get('DayArrangeSearch');
        if ($searchModel->from == null || $searchModel->to == null || $searchModel->gid == null) {
            $searchModel->from = date('Y-m-d', strtotime('-1 week'));
            $searchModel->to = date('Y-m-d', strtotime('tomorrow'));
        }
        if(isset($get['gid']) && !empty($get['gid'])){
            $gidStr = Json::encode($get['gid']);
        } else {
            $gidStr = Json::encode(array_keys(Game::gameDropDownData()));
        }
        $platformStr = '';
        if(isset($get['platform']) && !empty($get['platform'])){
            $platformStr = Json::encode($get['platform']);
        }

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'gidStr' => $gidStr,
            'platformStr' => $platformStr,
        ]);
    }


    public function actionPlatform()
    {
        $searchModel = new PaymentSearch();
        $get = Yii::$app->request->get('PaymentSearch');
        if ($searchModel->from == null || $searchModel->to == null || $searchModel->gid == null) {
            $searchModel->from = date('Y-m-d', strtotime('-1 week'));
            $searchModel->to = date('Y-m-d', strtotime('tomorrow'));
        }
        if(isset($get['gid']) && !empty($get['gid'])){
            $gidStr = Json::encode($get['gid']);
        } else {
            $gidStr = Json::encode(array_keys(Game::gameDropDownData()));
        }
        $platformStr = '';
        if(isset($get['platform']) && !empty($get['platform'])){
            $platformStr = Json::encode($get['platform']);
        }

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'gidStr' => $gidStr,
            'platformStr' => $platformStr,
        ]);
    }

    /**
     * Creates a new Payment model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Payment();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Payment model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Payment model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Payment model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Payment the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Payment::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}