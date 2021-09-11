<?php

namespace app\models;

use yii\db\ActiveRecord;

class Post extends ActiveRecord{

    public function getCategory(){
        return $this->hasOne(Category::class, ['categoy_id' => 'id']);
    }

    public function getUser(){
        return $this->hasOne(User::class, ['user_id' => 'id']);
    }
}