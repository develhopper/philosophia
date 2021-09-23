<?php

namespace app\classes;

use develhopper\Logos\Logos;
use Yii;
use yii\base\BootstrapInterface;

class LogosBuilder implements BootstrapInterface{
    
    /**
     * Logos instance
     *
     * @var Logos
     */
    private $logos;

    /**
     * Logos Commands registery
     *
     * @var LogosCommander
     */
    private $commander;

    /**
     * bootstrap function
     * 
     * Initialize Logos and add it's instance to DI container
     *
     * @param $app
     * @return void
     */
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

    /**
     * Register Logos Commands
     *
     * @return void
     */
    public function register(){
        $this->logos->register('test', [$this->commander, 'test']);
        $this->logos->register('ls', [$this->commander, 'ls']);
        $this->logos->register('cd {dir}', [$this->commander, 'cd']);
        $this->logos->register('login -u {username}', [$this->commander , 'login']);
        $this->logos->register('logout', [$this->commander, 'logout']);
        $this->logos->register('register -u {username}', [$this->commander, 'register']);
        $this->logos->register('mkdir {dir}', [$this->commander, 'mkdir']);
        $this->logos->register('rmdir {dir}', [$this->commander, 'rmdir']);
        $this->logos->register('rm {file}', [$this->commander, 'rm']);
        $this->logos->register('write', [$this->commander, 'write']);
        $this->logos->register('notepad {post}', [$this->commander, 'notepad']);
        $this->logos->register('view {post}', [$this->commander, 'view']);
        $this->logos->register('passwd', [$this->commander, 'passwd']);
        $this->logos->register('list roles', [$this->commander, 'list_roles']);
        $this->logos->register('list permissions', [$this->commander, 'list_permissions']);
        $this->logos->register('usermod {option} {role} {username}', [$this->commander, 'usermod']);
        $this->logos->register('whoami', [$this->commander, 'whoami']);
        $this->logos->register('whois {username}', [$this->commander, 'whois']);
    }
}