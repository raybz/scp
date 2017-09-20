<?php

namespace backend\controllers;

use backend\models\search\PaymentAnalysisSearch;
use common\models\Arrange;
use common\models\Platform;
use common\models\Server;
use yii\web\Controller;

class PaymentAnalysisController extends Controller
{
    public function actionIndex()
    {
        $searchModel = new PaymentAnalysisSearch();

        $searchModel->attributes = (\Yii::$app->request->get('PaymentAnalysisSearch'));
        if ($searchModel->from == null || $searchModel->to == null || $searchModel->game_id == null) {
            $searchModel->game_id = 1001;
            $searchModel->from = date('Y-m-d 00:00', strtotime('now'));
            $searchModel->to = date('Y-m-d H:i', strtotime('now'));
        }

        $diff = (strtotime($searchModel->to) - strtotime($searchModel->from)) / 86400;
        if ($diff > 1) {
            $from = $searchModel->from;
            $to = date('Y-m-d 23:59:59', strtotime($searchModel->to));
        } else {
            $from = date('Y-m-d H:i:00', strtotime($searchModel->from));
            $to = date('Y-m-d H:i:59', strtotime($searchModel->to));
        }

        if ($searchModel->platform_id == null) {
            $searchModel->platform_id = array_keys(Platform::platformDropDownData());
            $platformStr = serialize('');
        } else {
            $platformStr = serialize($searchModel->platform_id);
        }

        if ($searchModel->server_id == null) {
            $searchModel->server_id = Server::ServerDataDropData($searchModel->game_id, $searchModel->platform_id);
            $serverStr = serialize('');
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
                'from' => $from,
                'to' => $to,
            ]
        );
    }
}
