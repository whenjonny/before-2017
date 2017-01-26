<?php

$loader = new \Phalcon\Loader();

/**
 * We're a registering a set of directories taken from the configuration file
 */
// $loader->registerDirs(
//     array(
//         # $config->application->libraryDir,
//     )
// )->register();

// 注册命名空间性能优于注册目录
$loader->registerNamespaces(array(
    'Phalcon' => LIB_DIR . 'incubator'. __DS__ .'Library'. __DS__ .'Phalcon'. __DS__,
    // 模型层，主要是访问数据
    'Psgod\Models'  => APPS_DIR . 'models'  . __DS__,
    // 服务层，主要用于集合各个 models 间的数据供 controller 调用
    'Psgod\Services'=> APPS_DIR . 'services'. __DS__,
    // traits，用于类的 mix in
    'Psgod\Traits'	=> APPS_DIR . 'traits'  . __DS__,

))->register();

$loader->registerClasses(
    array(
        'Psgod\PsgodBaseController' => $config->application->libraryDir . 'PsgodBaseController.php',
        'OpenApiV3'                 => $config->application->libraryDir . 'txsdk/OpenApiV3.php',
        //todo: factory model about all page
        'CloudCDN'                  => $config->application->libraryDir . 'qiniu/qiniu.php',
        //youpai
        //'Cloud'                     => $config->application->libraryDir . 'youpai/youpai.php',
        'Page'                      => $config->application->libraryDir . 'think/page.php',
        'City'                      => $config->application->libraryDir . 'city/city.php',
        'AndroidUMeng'              => $config->application->libraryDir . 'umeng/AndroidUMeng.php',
        'iOSUMeng'                  => $config->application->libraryDir . 'umeng/iOSUMeng.php',
        'Msg'                       => $config->application->libraryDir . 'sms/sms.php',
        'Heartbeat'                 => $config->application->libraryDir . 'heartbeat/heartbeat.php'
    )
)->register();
