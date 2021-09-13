<?php

namespace app\classes;

use develhopper\Logos\Logos;
use Yii;
use yii\base\BootstrapInterface;

class LogosBuilder implements BootstrapInterface{
    
    private $logos;
    private $commander;

    public function bootstrap($app){
        $this->commander = new LogosCommander();
        Yii::$container->set(Logos::class, function($container, $params, $config){
            $this->logos = Logos::getInstance();
            $this->register();
            $this->logos->beforeRun([$this->commander, 'beforeRun']);
            $this->logos->afterRun([$this->commander, 'afterRun']);
            return $this->logos;
        });
    }

    public function register(){
        $this->logos->register('test', [$this->commander, 'test']);
        $this->logos->register('ls', [$this->commander, 'ls']);
        $this->logos->register('cd {dir}', [$this->commander, 'cd']);
        $this->logos->register('login -u {username}', [$this->commander , 'login']);
        $this->logos->register('logout', [$this->commander, 'logout']);
        $this->logos->register('mkdir {dir}', [$this->commander, 'mkdir']);
        $this->logos->register('rmdir {dir}', [$this->commander, 'rmdir']);
        $this->logos->register('rm {file}', [$this->commander, 'rm']);
        $this->logos->register('write', [$this->commander, 'write']);
        $this->logos->register('notepad {post}', [$this->commander, 'notepad']);
        $this->logos->register('view {post}', [$this->commander, 'view']);
    }
}