<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\RequestHistory;

class AjaxController extends Controller
{
    public $layout = false;
    
    public function beforeAction() {
        $response = Yii::$app->response;
        $response->format = \yii\web\Response::FORMAT_JSON;
        return true;
    }
    
    public function actionRequestHistory() {
        $history = RequestHistory::inst();

        $status = 'success';
        return [
            'meta' => ['status' => $status],
            'history' => $history->getData()
        ];
    }
}
