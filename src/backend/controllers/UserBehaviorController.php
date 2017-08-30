<?php
namespace backend\controllers;

use backend\models\search\PaymentAnalysisSearch;
use backend\models\search\UserBehaviorSearch;
use common\models\Arrange;
use common\models\Platform;
use yii\web\Controller;

class UserBehaviorController extends Controller
{
    public function actionSeep()
    {
        $searchModel = new UserBehaviorSearch();
        $searchModel->attributes = (\Yii::$app->request->get('UserBehaviorSearch'));
        if ($searchModel->from == null || $searchModel->to == null || $searchModel->game_id = null) {
            $searchModel->game_id = 1001;
            $searchModel->from = date('Y-m-d', strtotime('-1 month'));
            $searchModel->to = date('Y-m-d', strtotime('now'));
        }
        if($_type = \Yii::$app->request->get('_type')) {

            $searchModel->_type = $_type;
        }
        if ($searchModel->platform_id == null) {
            $searchModel->platform_id = array_keys(Platform::platformDropDownData());
            $platformStr = serialize($searchModel->platform_id);
        } else {
            $platformStr = serialize($searchModel->platform_id);
        }

        if ($searchModel->server_id == null) {
            $serverList = Arrange::getPaymentTopTenServer(
                '',
                '',
                $searchModel->game_id,
                $searchModel->platform_id,
                '',
                10,
                true
            );
            $searchModel->server_id = $serverList;
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
            $serverList = Arrange::getPaymentTopTenServer(
                '',
                '',
                $searchModel->game_id,
                $searchModel->platform_id,
                '',
                10,
                true
            );
            $searchModel->server_id = $serverList;
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

    public function actionMajor()
    {

    }

    public function actionLose()
    {

    }
}
