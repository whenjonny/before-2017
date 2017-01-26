<?php
namespace Psgod\Mobile;

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
            'Psgod\Mobile\Controllers'=> __DIR__ . __DS__ . 'controllers' . __DS__,
            'Psgod\Mobile\Models'      => __DIR__ . __DS__ . 'models'      . __DS__,
        ));

        $loader->register();
    }

    public function registerServices($di)
    {
        $di->set('dispatcher', function() {
            $dispatcher = new Dispatcher();
            $dispatcher->setDefaultNamespace('Psgod\Mobile\Controllers');

            return $dispatcher;
        });

        $di['view']->setViewsDir(__DIR__ . __DS__ . 'views' . __DS__);
    }
}
