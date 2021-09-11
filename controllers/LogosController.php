<?php

namespace app\controllers;

use develhopper\Logos\Logos;
use Yii;
use yii\rest\Controller;
use yii\web\NotFoundHttpException;

class LogosController extends Controller{

    public function actionCommand(Logos $logos){
        $command = Yii::$app->request->post('command');
        
        $result = $logos->run($command);
        
        if(!$command || $result === -1)
            throw new NotFoundHttpException('command not found');

        return $result;
    }
}