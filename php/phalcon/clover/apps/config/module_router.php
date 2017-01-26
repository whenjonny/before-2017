<?php
use Phalcon\Mvc\Router;

/**
 * 设置路由规则
 */
$di->set('router', function() use ($config) {
    $router  = new Router();
    $router->setDefaultModule("main");
    $router->setDefaultNamespace("Psgod\Main\Controllers");

    // 主站点(默认)模块
    $router->add("/", array(
        'module'    => 'main',
        'controller'=> 'ask',
        'action'    => 'hot',
    ));

    // Android api 模块
    $router->add('/v1/:controller/:action/:params', array(
        'namespace' => 'Psgod\Android\Controllers',
        'module'    => 'android',
        'controller'=> 1,
        'action'    => 2,
        'params'    => 3,
    ))->setHostName($config->host->android);

    // iOS api 模块
    $router->add('/v1/:controller/:action/:params', array(
        'namespace' => 'Psgod\iOS\Controllers',
        'module'    => 'ios',
        'controller'=> 1,
        'action'    => 2,
        'params'    => 3,
    ))->setHostName($config->host->ios);

    // mobile 模块
    $router->add('/:controller/:action/:params', array(
        'namespace' => 'Psgod\Mobile\Controllers',
        'module'    => 'mobile',
        'controller'=> 1,
        'action'    => 2,
        'params'    => 3,
    ))->setHostName($config->host->mobile);

    // 后台运营 模块
    $router->add('/:controller/:action/:params', array(
        'namespace' => 'Psgod\Admin\Controllers',
        'module'    => 'admin',
        'controller'=> 1,
        'action'    => 2,
        'params'    => 3,
    ))->setHostName($config->host->admin);

    $router->add('/', array(
        'namespace' => 'Psgod\Admin\Controllers',
        'module'    => 'admin',
        'controller'=> 'index',
        'action'    => 'index',
    ))->setHostName($config->host->admin);

    return $router;
});

/**
 * 设置模块信息
 */
$application->registerModules(array(
    'main' => array(
        'className' => 'Psgod\Main\Module',
        'path' => MODULES_DIR . 'main/Module.php' 
    ), 
    'admin' => array(
        'className' => 'Psgod\Admin\Module',
        'path' => MODULES_DIR . 'admin/Module.php' 
    ), 
    'android' => array(
        'className' => 'Psgod\Android\Module',
        'path' => MODULES_DIR . 'android/Module.php'
    ),
    'ios'   => array(
        'className' => 'Psgod\iOS\Module',
        'path' => MODULES_DIR . 'ios/Module.php'
    ),
    'mobile'   => array(
        'className' => 'Psgod\Mobile\Module',
        'path' => MODULES_DIR . 'mobile/Module.php'
    )
));
