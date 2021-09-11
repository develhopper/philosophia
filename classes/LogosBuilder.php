<?php

namespace app\classes;

use develhopper\Logos\Logos;
use Yii;
use yii\base\BootstrapInterface;

class LogosBuilder implements BootstrapInterface{
    
    private $logos;
    private $params = [];
    public function bootstrap($app){
        Yii::$container->set(Logos::class, function($container, $params, $config){
            $this->logos = Logos::getInstance();
            $this->register();
            $this->logos->beforeRun([$this, 'beforeRun']);
            return $this->logos;
        });
    }

    public function register(){
        $this->logos->register('test', [$this, 'test']);
        $this->logos->register('ls', [$this, 'ls']);
        $this->logos->register('cd {dir}', [$this, 'cd']);
    }

    public function beforeRun(){
        $this->params['pwd'] = $pwd = Yii::$app->request->post('pwd') ?? '/';
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
}