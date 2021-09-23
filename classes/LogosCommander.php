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
use yii\web\IdentityInterface;
use yii\web\NotFoundHttpException;
use yii\web\UnauthorizedHttpException;

class LogosCommander{
    /**
     * result parameters
     *
     * @var array
     */
    private $params = [];

    /**
     * Array of methods that need authentication and authorization
     *
     * @var array
     */
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
    
    /**
     * Shorthand of Yii::$app->request->post()
     *
     * @param mixed $key
     * @return void
     */
    private function post($key){
        return Yii::$app->request->post($key);
    }

    /**
     * Get identity of authenticated user
     *
     * @return IdentityInterface|User
     */
    private function auth(){
        return Yii::$app->user->identity;
    }

    /**
     * Get Current Directory
     *
     * @return string|null
     * @throws NotFoundHttpException if category not found 
     */
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

    /**
     * Run before excution of command
     *
     * check authentocation and authorization of user before execution of command
     * 
     * @param callable $closure
     * @return void
     */
    public function beforeRun(callable $closure){
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

    /**
     * Run after execution of command
     * 
     * modify result of executed command
     *
     * @param array $result
     * @return mixed
     */
    public function afterRun($result){
        $result = ArrayHelper::toArray($result);
        $result['_command'] = $this->params['cmd'];
        $result['_pwd'] = $this->params['pwd'];
        if($this->auth()){
            $result['_username'] = $this->auth()->username;
        }
        return $result;
    }

    /**
     * List of categories in current categories
     *
     * @return array
     */
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

    /**
     * Change current category
     *
     * @param string $category
     * @return array
     * @throws NotFoundHttpException if category not found
     */
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

    /**
     * Login user
     *
     * @param string $username
     * @return IdentityInterface
     * @throws UnauthorizedHttpException if username or password is incorrect
     */
    public function login($username){
        $model = new LoginForm();
        $model->password = $this->post('password');
        $model->username = $username;

        if($model->login()){
            return Yii::$app->user->identity;
        }
        
        throw new UnauthorizedHttpException('username or password is incorrect');
    }

    /**
     * Register user
     *
     * @param string $username
     * @return array
     * @throws BadRequestHttpException if password is empty
     * @throws ConflictHttpException if username is taken
     */
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

    /**
     * Create new category in current category
     *
     * @param string $name
     * @return Category
     */
    public function mkdir($name){
        $parent_id = $this->getCurrent();
        
        $category = new Category();
        $category->name = $name;
        $category->user_id = $this->auth()->id;
        $category->parent_id = $parent_id;
        
        if($category->save())
            return $category;
    }

    /**
     * Write Post
     *
     * @return array
     * @throws BadRequestHttpException if can't create new post
     */
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

    /**
     * Logout user
     *
     * @return array
     */
    public function logout(){
        Yii::$app->user->logout();
        return ['message' => 'logout'];
    }

    /**
     * Remove Category
     *
     * @param string $category
     * @return array
     * @throws NotFoundHttpException if category not found
     */
    public function rmdir($category){
        $category = Category::find()
        ->where(['parent_id' => $this->getCurrent()])
        ->andWhere(['name' => $category])->one();

        if(!$category)
            throw new NotFoundHttpException('Category not found');

        $category->delete();
        return ['message' => 'removed'];
    }

    /**
     * Remove a Post
     *
     * @param string $post
     * @return array
     * @throws NotFoundHttpException if post not found
     */
    public function rm($post){
        $post = Post::find()
        ->where(['category_id' => $this->getCurrent()])
        ->andWhere(['title' => $post])->one();

        if(!$post)
            throw new NotFoundHttpException('Post not found');

        $post->delete();
        return ['message' => 'removed'];
    }

    /**
     * This method will return the existing post title and body of editing
     * otherwise will return empty title and body 
     *
     * @param string $post
     * @return Post|array
     */
    public function notepad($post){
        $post = Post::find()
        ->where(['category_id' => $this->getCurrent()])
        ->andWhere(['title' => $post])->one();

        if($post)
            return $post;
        
        return ['title' => '', 'body' => ''];
    }

    /**
     * Return exisiting post for viewing
     *
     * @param string $post
     * @return Post
     * @throws NotFoundHttpException if post not found
     */
    public function view($post){
        $post = Post::find()
        ->where(['category_id' => $this->getCurrent()])
        ->andWhere(['title' => $post])->one();

        if(!$post)
            throw new NotFoundHttpException('Post not found');

        return $post;
    }

    /**
     * Change password
     *
     * @return void
     * @throws BadRequestHttpException if password is empty
     */
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

    /**
     * Get List of roles defined in system
     *
     * @return array
     */
    public function list_roles(){
        $auth = Yii::$app->authManager;
        
        return ['list' => $auth->getRoles()];
    }

    /**
     * Get List of permissions defined in system
     *
     * @return array
     */
    public function list_permissions(){
        $auth = Yii::$app->authManager;

        return ['list' => $auth->getPermissions()];
    }

    /**
     * Modify user role and permissions
     *
     * @param string $option
     * Available Options
     *  - \-ar: Assign role
     *  - \-dr: delete role
     *  - \-ap: assign permission
     *  - \-dp delete permission
     * 
     * @param string $role
     * @param string $username
     * @return array
     * @throws NotFoundHttpException if user, role or permission  not found
     * @throws BadRequestHttpException if invalid option passed to command
     */
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
            $auth->revoke($permission, $user->getId());
            return ['message' => 'Permission revoked'];
        }

        throw new BadRequestHttpException('Invalid Options');
    }

    /**
     * Get account information of given username
     *
     * @param string $username
     * @return array
     * @throws NotFoundHttpException if user not found
     */
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

    /**
     * Get Current user account info
     *
     * @return array
     */
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