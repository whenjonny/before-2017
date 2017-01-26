<?php
namespace Psgod\Admin;

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
            'Psgod\Admin\Controllers'=> __DIR__ . __DS__ . 'controllers' . __DS__
        ));

        $loader->register();
    }

    public function registerServices($di)
    {
        $di->set('dispatcher', function() {
            $dispatcher = new Dispatcher();
            $dispatcher->setDefaultNamespace('Psgod\Admin\Controllers');

            return $dispatcher;
        });

        $di['view']->setViewsDir(__DIR__ . __DS__ . 'views' . __DS__);
    }
}
