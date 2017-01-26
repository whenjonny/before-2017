<?php
namespace Psgod\Android;

use Phalcon\Loader,
    Phalcon\Mvc\Dispatcher,
    Phalcon\Mvc\View,
    Phalcon\Mvc\ModuleDefinitionInterface;

class Module implements ModuleDefinitionInterface
{
    
    public function registerAutoloaders() 
    {

        $loader = new Loader();

        $loader->registerNamespaces(array(
            'Psgod\Android\Controllers' => __DIR__ . __DS__ . 'controllers' . __DS__,
            'Psgod\Android\Models'      => __DIR__ . __DS__ . 'models'      . __DS__,
        ));

        //记录每个url的访问
        $GLOBALS['di']['sys_log']->log(json_encode(array_merge($_REQUEST, $_COOKIE)));
        $loader->register();
    }

    public function registerServices($di)
    {
        
    }
}
