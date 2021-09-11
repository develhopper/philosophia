<?php

namespace app\controllers;

use app\models\LoginForm;
use Yii;
use yii\rest\Controller;
use yii\web\UnauthorizedHttpException;

class UserController extends Controller{
    
    public function actionLogin(){
        $model = new LoginForm();

        $model->attributes = Yii::$app->request->post();

        if($model->login()){
            return Yii::$app->user->identity;
        }else{
            throw new UnauthorizedHttpException("Username or Password is wrong");
        }
    }

    public function actionRegister(){
        $model = new LoginForm();

        $model->attributes = Yii::$app->request->post();

    }
}