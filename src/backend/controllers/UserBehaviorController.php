<?php
namespace backend\controllers;

use backend\models\search\PaymentAnalysisSearch;
use common\models\Server;
use common\models\Platform;
use yii\web\Controller;

class UserBehaviorController extends Controller
{
    public function actionSeep()
    {

        $searchModel = new PaymentAnalysisSearch();
        $searchModel->attributes = (\Yii::$app->request->get('PaymentAnalysisSearch'));
        if ($searchModel->from == null || $searchModel->to == null || $searchModel->game_id = null) {
            $searchModel->game_id = 1001;
            $searchModel->from = date('Y-m-d', strtotime('-3 week'));
            $searchModel->to = date('Y-m-d', strtotime('now'));
        }
        if(\Yii::$app->request->get('_type')) {
            $searchModel->from = date('Y-m-d', strtotime('now'));
        }
        if ($searchModel->platform_id == null) {
            $searchModel->platform_id = array_keys(Platform::platformDropDownData());
            $platformStr = serialize($searchModel->platform_id);
        } else {
            $platformStr = serialize($searchModel->platform_id);
        }

        if ($searchModel->server_id == null) {
            $searchModel->server_id = array_keys(
                Server::ServerDataDropData($searchModel->game_id, $searchModel->platform_id)
            );
            $serverStr = serialize($searchModel->server_id);
        } else {
            $serverStr = serialize($searchModel->server_id);
        }
        $dataProvider = $searchModel->search();

        return $this->render(
            'seep',
            [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'platformStr' => $platformStr,
                'serverStr' => $serverStr,
                'from' => $searchModel->from,
                'to' => $searchModel->to,
            ]
        );
    }

    public function actionHabit()
    {

    }

    public function actionMajor()
    {

    }

    public function actionLose()
    {

    }
}
