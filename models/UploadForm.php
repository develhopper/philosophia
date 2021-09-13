<?php

namespace app\models;

use Yii;
use yii\base\Model;

class UploadForm extends Model{

    public $upload;

    public function rule(){
        return [
            [['upload'], 'file', 'extensions' =>'png, jpg']
        ];
    }

    public function upload(){
        if($this->validate()){
            $filename = 'upload/'.Yii::$app->security->generateRandomString(15) . '.'.$this->upload->extension;
            $this->upload->saveAs($filename);
            return $filename;
        }
    }
}