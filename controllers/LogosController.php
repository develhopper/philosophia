<?php

namespace app\controllers;

use develhopper\Logos\Logos;
use Yii;
use yii\rest\Controller;
use yii\web\NotFoundHttpException;

class LogosController extends Controller{

    /**
     * Get command action
     * 
     * Load Logos using DI Container
     * 
     * pass command to $logos->run() and return result as response
     * 
     *
     * @param Logos $logos
     * @return array
     * @throws NotFoundHttpException if command not found
     */
    public function actionCommand(Logos $logos){
        $command = Yii::$app->request->post('command');
        
        $result = $logos->run($command);
        
        if(!$command || $result === -1)
            throw new NotFoundHttpException('command not found');

        return $result;
    }
}