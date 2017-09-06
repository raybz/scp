<?php

namespace backend\controllers;

use backend\models\search\GamePaymentSearch;
use backend\models\search\OrderMatchSearch;
use backend\models\search\PaymentSearch;
use backend\models\search\PlatformPaymentSearch;
use backend\models\search\ServerPaymentSearch;
use common\models\Arrange;
use common\models\Game;
use common\models\Payment;
use common\models\Platform;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;
use yii\web\UploadedFile;

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
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new GamePaymentSearch();
        $searchModel->attributes = Yii::$app->request->get('GamePaymentSearch');
        if ($searchModel->from == null || $searchModel->go == null) {
            $searchModel->from = date('Y-m-d', strtotime('-1 week'));
            $searchModel->go = date('Y-m-d', strtotime('now'));
            $searchModel->to = date('Y-m-d', strtotime('tomorrow'));
        } else {
            $searchModel->to = date('Y-m-d', strtotime($searchModel->go.'+1 day'));
        }
        if ($searchModel->game_id == null) {
            $searchModel->game_id = array_keys(Game::gameDropDownData());
            $gidStr = serialize($searchModel->game_id);
        } else {
            $gidStr = serialize($searchModel->game_id);
        }

        if ($searchModel->platform_id == null) {
            $searchModel->platform_id = array_keys(Platform::platformDropDownData());
            $platformStr = serialize($searchModel->platform_id);
        } else {
            $platformStr = serialize($searchModel->platform_id);
        }
        $dataProvider = $searchModel->search();

        return $this->render(
            'index',
            [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'gidStr' => $gidStr,
                'platformStr' => $platformStr,
            ]
        );
    }


    public function actionPlatform()
    {
        $searchModel = new PlatformPaymentSearch();
        $searchModel->attributes = Yii::$app->request->get('PlatformPaymentSearch');
        if ($searchModel->from == null || $searchModel->go == null) {
            $searchModel->from = date('Y-m-d', strtotime('-1 week'));
            $searchModel->go = date('Y-m-d', strtotime('now'));
            $searchModel->to = date('Y-m-d', strtotime('tomorrow'));
        } else {
            $searchModel->to = date('Y-m-d', strtotime($searchModel->go.'+1 day'));
        }
        if ($searchModel->game_id == null) {
            $searchModel->game_id = array_keys(Game::gameDropDownData());
            $gidStr = serialize($searchModel->game_id);
        } else {
            $gidStr = serialize($searchModel->game_id);
        }
        if ($searchModel->platform_id == null) {
            $searchModel->platform_id = array_keys(Platform::platformDropDownDataOrderByPay());
            $platformStr = serialize($searchModel->platform_id);
        } else {
            $platformStr = serialize($searchModel->platform_id);
        }

        $dataProvider = $searchModel->search();

        return $this->render(
            'platform',
            [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'gidStr' => $gidStr,
                'platformStr' => $platformStr,
            ]
        );
    }

    public function actionServer()
    {
        $searchModel = new ServerPaymentSearch();
        $searchModel->attributes = Yii::$app->request->get('ServerPaymentSearch');
        if ($searchModel->from == null || $searchModel->go == null || $searchModel->game_id = null) {
            $searchModel->game_id = 1001;
            $searchModel->from = date('Y-m-d', strtotime('-1 week'));
            $searchModel->go = date('Y-m-d', strtotime('now'));
            $searchModel->to = date('Y-m-d', strtotime('tomorrow'));
        } else {
            $searchModel->to = date('Y-m-d', strtotime($searchModel->go.'+1 day'));
        }

        if ($searchModel->platform_id == null) {
            $searchModel->platform_id = array_keys(Platform::platformDropDownData());
            $platformStr = serialize($searchModel->platform_id);
        } else {
            $platformStr = serialize($searchModel->platform_id);
        }

        if ($searchModel->server_id == null) {
            $serverList = Arrange::getPaymentTopTenServer(
                $searchModel->from,
                $searchModel->to,
                $searchModel->game_id,
                $searchModel->platform_id,
                '',
                10,
                true
            );
            $searchModel->server_id = $serverList ?: [];
            $serverStr = serialize($searchModel->server_id);
        } else {
            $serverStr = serialize($searchModel->server_id);
        }

        $dataProvider = $searchModel->search();

        return $this->render(
            'server',
            [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'platformStr' => $platformStr,
                'serverStr' => $serverStr,
            ]
        );
    }

    public function actionList()
    {
        $searchModel = new PaymentSearch();
        if ($searchModel->from == null || $searchModel->to == null) {
            $searchModel->game_id = 1001;
            $searchModel->from = date('Y-m-d', strtotime('-1 month'));
            $searchModel->to = date('Y-m-d', strtotime('now'));
        }
        if ($searchModel->platform_id == null) {
            $searchModel->platform_id = array_keys(Platform::platformDropDownData());
        }
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $total_payment = Payment::getTotalCoinMoney(
            $searchModel->from,
            $searchModel->to,
            $searchModel->game_id,
            $searchModel->platform_id
        );

        return $this->render(
            'list',
            [
                'total_coins' => round($total_payment['total_coins'] ?? 0, 2),
                'total_money' => round($total_payment['total_money'] ?? 0 / 100, 2),
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]
        );
    }

    public function actionMatch()
    {
        if(Yii::$app->request->get()) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $get = Yii::$app->request->get('PaymentSearch');
            $filename = Yii::getAlias('@backend').'/file/aaa.csv';
//            ini_set('max_execution_time', '0');
            ignore_user_abort();
            set_time_limit(0);

//            $result = OrderMatch::fileContext($filename, $get['platform_id'],$get['from'], $get['to']);
//            return $result;
        }

        $searchModel = new OrderMatchSearch();
        if ($searchModel->from == null || $searchModel->to == null) {
            $searchModel->game_id = 1001;
            $searchModel->platform_id = 14;
            $searchModel->batch = 744;
            $searchModel->from = date('Y-m-01', strtotime('-1 month'));
            $searchModel->to = date('Y-m-01', strtotime('now'));
        }

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('match', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionUpload()
    {
        $file = UploadedFile::getInstanceByName('file');
//        $filename = Yii::getAlias('@backend').'/file/'.time().'_'.mt_rand(100, 999).'.'.$file->extension;
        $filename = Yii::getAlias('@backend').'/file/aaa.'.$file->extension;
        $file->saveAs($filename);
        $_SESSION['_file_name'] = $filename;
        var_dump($_SESSION);
    }

}