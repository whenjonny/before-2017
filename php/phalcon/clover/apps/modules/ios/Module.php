<?php
namespace Psgod\iOS;

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
            'Psgod\iOS\Controllers' => __DIR__ . __DS__ . 'controllers' . __DS__,
        ));

        $loader->register();
    }

    public function registerServices($di)
    {
        
    }
}