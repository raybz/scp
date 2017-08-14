<?php

namespace backend\controllers;

use backend\models\search\ServerPaymentSearch;
use common\models\GamePlatformServer;
use common\models\Platform;
use yii\web\Controller;

class PaymentAnalysisController extends Controller
{
    public function actionIndex()
    {
        $searchModel = new ServerPaymentSearch();
        $searchModel->attributes = \Yii::$app->request->get('ServerPaymentSearch');
        if ($searchModel->from == null || $searchModel->to == null || $searchModel->game_id = null) {
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
            $searchModel->server_id = array_keys(
                GamePlatformServer::ServerDataDropData($searchModel->game_id, $searchModel->platform_id)
            );
            $serverStr = serialize($searchModel->server_id);
        } else {
            $serverStr = serialize($searchModel->server_id);
        }
        $dataProvider = $searchModel->search();

        return $this->render(
            'index',
            [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'platformStr' => $platformStr,
                'serverStr' => $serverStr,
            ]
        );
    }
}
