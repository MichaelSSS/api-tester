<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\UrlForm;
use app\models\RequestHistory;

class SiteController extends Controller
{
    public function actionIndex() {
        $model = new UrlForm();
        $history = RequestHistory::inst();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $model->open();
            
            $this->layout = false;
            $response = Yii::$app->response;
            $response->format = \yii\web\Response::FORMAT_JSON;
            
            $status = 'success';
            $data = [
                'method' => $model->method,
                'url' => $model->url,
                'params' => $model->params,
                'date' => $model->date,
                'status_code' => $model->status,
                'response' => $model->response,
            ];
            
            $history->add($model->method, $model->url, $model->params, $model->date);
            
            if ($model->error !== '') {
                $status = 'error';
                $data['error'] = $model->error;
            }
                
            return [
                'meta' => ['status' => $status],
                'data' => $data
            ];
        }
        
        return $this->render('index', [
            'model' => $model
        ]);
    }
}
