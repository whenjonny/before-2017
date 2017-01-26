<?php
//session_id("3b4uii2s7ekassa61pifmo83e1");
//@session_start();
//@session_start();

//session_regenerate_id();方法用于改变当前session_id的值，并保留session中数组的值。参数默认为false,如果设置为true则改变session_id的值，并清空当前session数组。
//session_regenerate_id(true);


try {

    /**
     * Read the configuration
     */
    $config = include __DIR__ . "/../apps/config/config.php";

    /**
     * Read auto-loader
     */
    include __DIR__ . "/../apps/config/loader.php";

    /**
     * Read services
     */
    include __DIR__ . "/../apps/config/services.php";

    /**
     * bootstrap, include functions
     */
    include __DIR__ . "/../apps/common.php";

    /**
     * Handle the request
     */
    $application = new \Phalcon\Mvc\Application($di);

    include __DIR__ . "/../apps/config/module_router.php";

    echo $application->handle()->getContent();

} catch (\Exception $e) {
    echo $e->getMessage();
}
