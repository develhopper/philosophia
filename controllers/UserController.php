<?php

namespace app\controllers;

use app\models\LoginForm;
use app\models\User;
use Yii;
use yii\rest\Controller;
use yii\web\BadRequestHttpException;
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
        $username = Yii::$app->request->post('username');
        $password = Yii::$app->request->post('password');

        if($username && $password){
            $user = new User();
            $user->username = $username;
            $user->password = Yii::$app->getSecurity()->generatePasswordHash($password);
            if($user->save()){
                return $user;
            }
        }else{
            throw new BadRequestHttpException('Bad request parameters');
        }

    }
}