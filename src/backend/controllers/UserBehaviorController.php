<?php
namespace backend\controllers;

use backend\models\search\PaymentAnalysisSearch;
use backend\models\search\UserBehaviorSearch;
use common\models\Arrange;
use common\models\Platform;
use common\models\Server;
use yii\helpers\Json;
use yii\web\Controller;

class UserBehaviorController extends Controller
{
    public function actionSeep()
    {
        $searchModel = new UserBehaviorSearch();
        $searchModel->attributes = (\Yii::$app->request->get('UserBehaviorSearch'));
        if ($searchModel->from == null || $searchModel->to == null || $searchModel->game_id == null) {
            $searchModel->game_id = 1001;
            $searchModel->from = date('Y-m-01');
            $searchModel->to = date('Y-m-d', strtotime('yesterday'));
        }
        if($_type = \Yii::$app->request->get('_type')) {

            $searchModel->_type = $_type;
        }
        if ($searchModel->platform_id == null) {
            $searchModel->platform_id = array_keys(Platform::platformDropDownData());
            $platformStr = serialize('');
        } else {
            $platformStr = serialize($searchModel->platform_id);
        }

        if ($searchModel->server_id == null) {
            $searchModel->server_id = array_keys(Server::ServerDataDropData($searchModel->game_id, $searchModel->platform_id));
            $serverStr = serialize('');
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
        $searchModel = new UserBehaviorSearch();
        $searchModel->load(\Yii::$app->request->get());
        if ($searchModel->from == null || $searchModel->to == null || $searchModel->game_id == null) {
            $searchModel->game_id = 1001;
            $searchModel->from = date('Y-m-01');
            $searchModel->to = date('Y-m-d', strtotime('now'));
        }
        if($_type = \Yii::$app->request->get('_type')) {
            $searchModel->_type = $_type;
        }
        if ($searchModel->platform_id == null) {
            $searchModel->platform_id = array_keys(Platform::platformDropDownData());
            $platformStr = Json::encode('');
        } else {
            $platformStr = Json::encode($searchModel->platform_id);
        }
        if ($searchModel->server_id == null) {
            $searchModel->server_id = array_keys(Server::ServerDataDropData($searchModel->game_id, $searchModel->platform_id));
            $serverStr = Json::encode('');
        } else {
            $serverStr = Json::encode($searchModel->server_id);
        }

        return $this->render(
            'habit',
            [
                'searchModel' => $searchModel,
                'platformStr' => $platformStr,
                'serverStr' => $serverStr,
                'to' => $searchModel->to.' 23:59:59'
            ]
        );
    }
}
