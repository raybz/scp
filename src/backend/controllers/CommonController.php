<?php
namespace backend\controllers;

use backend\models\search\DashBoardSearch;
use common\models\Game;
use yii\web\Controller;

class CommonController extends Controller
{
    /**
     * 平台近日概况
     */
    public function actionDashboard()
    {
        $searchModel = new DashBoardSearch();
        $get = \Yii::$app->request->get('DashBoardSearch');
        $searchModel->attributes = $get;
        if ($searchModel->to == null || $searchModel->gid == null) {
            $searchModel->gid = 1001;
            $searchModel->to = date('Y-m-d', strtotime('yesterday'));
        } else {
            $searchModel->to = $get['to'];
        }
        $treeDay = clone $searchModel;
        $oneMonth = clone $searchModel;
        $searchModel->type = DashBoardSearch::TWO_DAY;
        $treeDay->type = DashBoardSearch::THREE_DAY;
        $oneMonth->type = DashBoardSearch::MONTH;
        $dataProvider = $searchModel->search();
        $threeDayDataProvider = $treeDay->search();
        $monthDataProvider = $oneMonth->search();

        return $this->render(
            'dashboard'
            ,[
                'searchModel' =>$searchModel,
                'dataProvider' =>$dataProvider,
                'threeDayDataProvider' =>$threeDayDataProvider,
                'monthDataProvider' =>$monthDataProvider,
            ]
        );
    }
}
