<?php

use app\models\User;
use yii\db\Migration;

/**
 * Class m210913_125422_init_rbac
 */
class m210913_125422_init_rbac extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = Yii::$app->authManager;

        $rules = [
            'author' => [
                'create_post' => 'Create a Post'
            ],
            'admin' => [
                'create_category' => 'Create a Category',
                'remove_category' => 'remove a category',
                'remove_post' => 'Remove a post',
                'update_post' => 'Update a Post',
                'assing_user_roles' => 'Change user permissions and roles',
                'create_post' => 'Create a Post'
            ]
        ];

        $permissions = [];
        $roles = [];
        foreach($rules as $role_name=>$role_permissions){
            $role = $auth->createRole($role_name);
            $auth->add($role);
            $roles[$role_name] = $role;

            foreach($role_permissions as $name=>$description){
                if(!isset($permissions[$name])){
                    $permissions[$name] = $this->createPermission($auth, $name, $description);
                    $auth->add($permissions[$name]);
                }
                $auth->addChild($role, $permissions[$name]);
            }
        }

        $user = User::findOne(1);
        if(!$user){
            $user = new User();
            $user->username = 'alireza';
            $user->password = Yii::$app->security->generatePasswordHash('password');
            $user->save();
        }
        $auth->assign($roles['admin'], $user->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $auth = Yii::$app->authManager;

        $auth->removeAll();
    }

    public function createPermission($auth, $name, $description){
        $permission = $auth->createPermission($name);
        $permission->description = $description;
        return $permission;
    }
}
