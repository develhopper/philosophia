<?php

namespace app\controllers;

use app\models\UploadForm;
use Yii;
use yii\filters\AccessControl;
use yii\rest\Controller;
use yii\web\MethodNotAllowedHttpException;
use yii\web\UploadedFile;
/**
 * FileController used for handling upload requests
 */
class FileController extends Controller{
    
    /**
     * allow only authenticated users
     *
     * @return array
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['upload'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['upload'],
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * upload action
     *
     * @return array
     * @throws MethodNotAllowedHttpException if method not allowed
     */
    public function actionUpload(){
        $model = new UploadForm();
        
        if (Yii::$app->request->isPost) {
            $model->upload = UploadedFile::getInstanceByName('upload');
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ['url' => $model->upload()];
        }else{
            throw new MethodNotAllowedHttpException('Method not allowed');
        }
    }

}