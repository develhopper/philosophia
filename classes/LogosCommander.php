<?php

namespace app\classes;

use app\models\Category;
use app\models\LoginForm;
use app\models\Post;
use app\models\User;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\ConflictHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UnauthorizedHttpException;

class LogosCommander{
    private $params = [];
    private $required_auth = [
        'auth' =>['write','mkdir', 'rm', 'rmdir', 'notepad', 'passwd'],
        'can' =>[
            'create_post' => ['notepad', 'write'],
            'create_category' => ['mkdir'],
            'remove_category' => ['rmdir'],
            'assign_user_roles' => ['usermod', 'list_roles', 'list_permissions'],
            'remove_post' => ['rm']
        ]
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
        $this->params['cmd'] = $closure[1];
        if(in_array($closure[1], $this->required_auth['auth'])){
            if(!$this->auth()){
                throw new UnauthorizedHttpException('Unauthorized action');
            }
        }

        foreach($this->required_auth['can'] as $permission=>$actions){
            if(in_array($closure[1], $actions)){
                if(!$this->auth()){
                    throw new UnauthorizedHttpException('Unauthorized action');
                }
                if(!Yii::$app->user->can($permission)){
                    throw new UnauthorizedHttpException('You dont have permission');
                }
            }
        }
    }

    public function afterRun($result){
        $result = ArrayHelper::toArray($result);
        $result['_command'] = $this->params['cmd'];
        $result['_pwd'] = $this->params['pwd'];
        if($this->auth()){
            $result['_username'] = $this->auth()->username;
        }
        return $result;
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
        if($category == "/"){
            $this->params['pwd'] = '/';
            return ['pwd' => $category];
        }

        $category = Category::find()
        ->where(['parent_id' => $this->getCurrent()])
        ->andWhere(['name' => $category])->one();
        if($category){
            if($this->params['pwd'] == '/')
                $this->params['pwd'] = '';
            $this->params['pwd'] = $this->params['pwd']. '/' . $category->name;
            return [];
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

    public function register($username){
        $user = User::find()->where(['username' => $username])->one();
        $password = $this->post('password');

        if(!$password)
            throw new BadRequestHttpException('Password can not be empty');

        if($user)
            throw new ConflictHttpException('Username is taken');

        $user = new User();
        $user->username = $username;
        $user->password = Yii::$app->security->generatePasswordHash($password);

        if($user->save())
            return ['message' => 'Registered'];
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
        $id = $this->post('id');
        $update = $this->post('update');
        
        if($id && $update){
            $post = Post::findOne($id);
            if($post){
                $post->title = $this->post('title');
                $post->body = $this->post('body');
            }
        }else{
            $post = new Post();
            $post->title = $this->post('title');
            $post->body = $this->post('body');
            $post->category_id = $this->getCurrent();
            $post->user_id = $this->auth()->id;
        }

        if($post->save()){
            return ['message' => 'saved'];
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

    public function notepad($post){
        $post = Post::find()
        ->where(['category_id' => $this->getCurrent()])
        ->andWhere(['title' => $post])->one();

        if($post)
            return $post;
        
        return ['title' => '', 'body' => ''];
    }

    public function view($post){
        $post = Post::find()
        ->where(['category_id' => $this->getCurrent()])
        ->andWhere(['title' => $post])->one();

        if(!$post)
            throw new NotFoundHttpException('Post not found');

        return $post;
    }

    public function passwd(){
        $password = $this->post('password');
        if(!$password)
            throw new BadRequestHttpException('Password can not be empty');
        $user = $this->auth();
        $user->password = Yii::$app->security->generatePasswordHash($password);
        if($user->save()){
            return ['message' => 'password has been changed'];
        }
    }

    public function list_roles(){
        $auth = Yii::$app->authManager;
        
        return ['list' => $auth->getRoles()];
    }

    public function list_permissions(){
        $auth = Yii::$app->authManager;

        return ['list' => $auth->getPermissions()];
    }

    public function usermod($option, $role, $username){
        $user = User::find()->where(['username' => $username])->one();
        $auth = Yii::$app->authManager;
        if(!$user)
            throw new NotFoundHttpException('User not found');
        
        if($option == "-ar"){
            $role = $auth->getRole($role);
            if(!$role)
                throw new NotFoundHttpException('Role not found');
            $auth->assign($role, $user->getId());
            return ['message' => 'Role assigned to user'];
        }
        
        if($option == "-ap"){
            $permission = $auth->getPermission($role);
            if(!$permission)
                throw new NotFoundHttpException('Permission not found');
            $auth->assign($permission, $user->getId());
            return ['message' => 'Permission assigned to user'];
        }

        if($option == "-dr"){
            $role = $auth->getRole($role);
            if(!$role)
                throw new NotFoundHttpException('Role not found');
            $auth->revoke($role, $user->getId());
            return ['message' => 'Role revoked'];
        }

        if($option == "-dp"){
            $permission = $auth->getPermission($role);
            if(!$permission)
                throw new NotFoundHttpException('Permission not found');
            $auth->assign($permission, $user->getId());
            return ['message' => 'Permission revoked'];
        }

        throw new BadRequestHttpException('Invalid Options');
    }

    public function whois($username){
        $user = User::find()->where(['username' => $username])->one();
        if(!$user)
            throw new NotFoundHttpException('User not found');

        $auth = Yii::$app->authManager;

        $roles = array_keys($auth->getRolesByUser($user->id));
        $permissions = array_keys($auth->getPermissionsByUser($user->id));
        return ['data' =>[
            'user' => $user->username,
            'roles' => $roles,
            'permissions' => $permissions
        ]];
    }

    public function whoami(){
        if($this->auth()){
            $auth = Yii::$app->authManager;

            $roles = array_keys($auth->getRolesByUser($this->auth()->getId()));
            $permissions = array_keys($auth->getPermissionsByUser($this->auth()->getId()));
            return ['data' =>[
                'user' => $this->auth()->username,
                'roles' => $roles,
                'permissions' => $permissions
            ]];
        }
        return ['data' => ['user' => 'MR. Nobody']];
    }
}