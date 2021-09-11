<?php

namespace app\classes;

use app\models\Category;
use app\models\LoginForm;
use Yii;

class LogosCommander{
    private $params = [];
    
    private function post($key){
        return Yii::$app->request->post($key);
    }

    private function auth(){
        return Yii::$app->user->identity;
    }

    private function getParent(){
        $parents = explode('/',$this->params['pwd']);
        $parent = end($parents);
        if($parent){
            $category = Category::find()->where(['name' => $parent])->one();
            return $category->id;
        }
        return null;
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

    public function mkdir($name){
        $parent_id = $this->getParent();
        
        $category = new Category();
        $category->name = $name;
        $category->user_id = $this->auth()->id;
        $category->parent_id = $parent_id;
        
        if($category->save())
            return $category;
    }
}