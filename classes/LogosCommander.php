<?php

namespace app\classes;

use app\models\Category;
use app\models\LoginForm;
use app\models\Post;
use PDO;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UnauthorizedHttpException;

class LogosCommander{
    private $params = [];
    private $required_auth = [
        'write', 'mkdir', 'rm', 'rmdir'
    ];
    
    private function post($key){
        return Yii::$app->request->post($key);
    }

    private function auth(){
        return Yii::$app->user->identity;
    }

    private function getCurrent(){
        $parents = explode('/',$this->params['pwd']);
        $parent = end($parents);
        if($parent){
            $category = Category::find()->where(['name' => $parent])->one();
            if(!$category)
                throw new NotFoundHttpException('category not found');
            return $category->id;
        }
        return null;
    }

    public function beforeRun($closure){
        $this->params['pwd'] = $this->post('pwd') ?? '/';
        if(in_array($closure[1], $this->required_auth)){
            if(!$this->auth()){
                throw new UnauthorizedHttpException('Unauthorized action');
            }
        }
    }

    public function test(){
        return [
            'message' => 'succeed'
        ];
    }

    public function ls(){
        $categories = Category::find()
        ->where(['parent_id' => $this->getCurrent()])->all();
        
        $posts = Post::find()->select(['id','title'])
        ->where(['category_id' => $this->getCurrent()])->all();
        
        return [
            'categories' => $categories,
            'posts' => $posts
        ];
    }

    public function cd($category){
        if($category == "/")
            return ['pwd' => $category];

        $category = Category::find()
        ->where(['parent_id' => $this->getCurrent()])
        ->andWhere(['name' => $category])->one();
        if($category){
            return ['pwd' => $this->params['pwd'] . $category->name];
        }

        throw new NotFoundHttpException('Category not found');
    }

    public function login($username){
        $model = new LoginForm();
        $model->password = $this->post('password');
        $model->username = $username;

        if($model->login()){
            return Yii::$app->user->identity;
        }
        
        throw new UnauthorizedHttpException('username or password is incorrect');
    }

    public function mkdir($name){
        $parent_id = $this->getCurrent();
        
        $category = new Category();
        $category->name = $name;
        $category->user_id = $this->auth()->id;
        $category->parent_id = $parent_id;
        
        if($category->save())
            return $category;
    }

    public function write(){
        $post = new Post();
        $post->title = $this->post('title');
        $post->body = $this->post('body');
        $post->category_id = $this->getCurrent();
        $post->user_id = $this->auth()->id;

        if($post->save()){
            return $post;
        }

        throw new BadRequestHttpException('cant create post');
    }

    public function logout(){
        Yii::$app->user->logout();
        return ['message' => 'logout'];
    }

    public function rmdir($category){
        $category = Category::find()
        ->where(['parent_id' => $this->getCurrent()])
        ->andWhere(['name' => $category])->one();

        if(!$category)
            throw new NotFoundHttpException('Category not found');

        $category->delete();
        return ['message' => 'removed'];
    }

    public function rm($post){
        $post = Post::find()
        ->where(['category_id' => $this->getCurrent()])
        ->andWhere(['title' => $post])->one();

        if(!$post)
            throw new NotFoundHttpException('Post not found');

        $post->delete();
        return ['message' => 'removed'];
    }
}