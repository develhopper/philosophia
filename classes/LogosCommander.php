<?php

namespace app\classes;

use app\models\LoginForm;
use Yii;

class LogosCommander{
    private $params = [];
    
    private function post($key){
        return Yii::$app->request->post($key);
    }

    public function beforeRun(){
        $this->params['pwd'] = $this->post('pwd') ?? '/';
    }

    public function test(){
        return [
            'message' => 'succeed'
        ];
    }

    public function ls(){

    }

    public function cd($input){
        return $this->params['pwd'] . $input;
    }

    public function login($username){
        $model = new LoginForm();
        $model->password = $this->post('password');
        $model->username = $username;

        if($model->login()){
            return Yii::$app->user->identity;
        }
        return ['status' => false , 'message' => 'username or password is incorrect'];
    }
}