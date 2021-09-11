<?php

namespace app\models;

use yii\db\ActiveRecord;

class Category extends ActiveRecord{

    public function getParent(){
        return $this->hasOne(Category::class, ['parent_id' => 'id']);
    }
}