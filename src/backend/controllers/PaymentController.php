<?php

namespace backend\controllers;

use backend\models\search\PlatformPaymentSearch;
use backend\models\search\DayArrangeSearch;
use common\models\Game;
use common\models\Platform;
use Yii;
use common\models\Payment;
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
        if(isset($get['platform']) && !empty($get['platform'])){
            $platformStr = Json::encode($get['platform']);
        } else {
            $platformStr = Json::encode(array_keys(Platform::platformDropDownData()));
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
        $searchModel = new PlatformPaymentSearch();
        $get = Yii::$app->request->get('PlatformPaymentSearch');
        $searchModel->attributes = $get;
        if ($searchModel->from == null || $searchModel->to == null) {
            $searchModel->from = date('Y-m-d', strtotime('-1 week'));
            $searchModel->go = date('Y-m-d', strtotime('now'));
            $searchModel->to = date('Y-m-d', strtotime('tomorrow'));
        } else {
            $searchModel->to = date('Y-m-d', strtotime($searchModel->go. '+1 day'));
        }
        if(isset($get['gid']) && !empty($get['gid'])){
            $gidStr = Json::encode($get['gid']);
        } else {
            $gidStr = Json::encode(array_keys(Game::gameDropDownData()));
        }
        if(isset($get['platform']) && !empty($get['platform'])){
            $platformStr = Json::encode($get['platform']);
        } else {
            $platformStr = Json::encode(array_keys(Platform::platformDropDownData()));
        }
        $dataProvider = $searchModel->search();

        return $this->render('platform', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'gidStr' => $gidStr,
            'platformStr' => $platformStr,
        ]);
    }

    public function actionServer()
    {
        $searchModel = new PlatformPaymentSearch();
        $get = Yii::$app->request->get('PlatformPaymentSearch');
        $searchModel->attributes = $get;
        if ($searchModel->from == null || $searchModel->to == null) {
            $searchModel->from = date('Y-m-d', strtotime('-1 week'));
            $searchModel->go = date('Y-m-d', strtotime('now'));
            $searchModel->to = date('Y-m-d', strtotime('tomorrow'));
        } else {
            $searchModel->to = date('Y-m-d', strtotime($searchModel->go. '+1 day'));
        }
        if(isset($get['gid']) && !empty($get['gid'])){
            $gidStr = Json::encode($get['gid']);
        } else {
            $gidStr = Json::encode(array_keys(Game::gameDropDownData()));
        }
        if(isset($get['platform']) && !empty($get['platform'])){
            $platformStr = Json::encode($get['platform']);
        } else {
            $platformStr = Json::encode(array_keys(Platform::platformDropDownData()));
        }
        $dataProvider = $searchModel->search();

        return $this->render('server', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'gidStr' => $gidStr,
            'platformStr' => $platformStr,
        ]);
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