<?php
use Phalcon\DI\FactoryDefault;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Url as UrlResolver;
use Phalcon\Mvc\View\Engine\Volt as VoltEngine;
use Phalcon\Mvc\Model\Metadata\Memory as MetaDataAdapter;
use Phalcon\Session\Adapter\Files as SessionAdapter;
use Phalcon\Logger\Formatter\Line as LineFormatter;

/**
 * The FactoryDefault Dependency Injector automatically register the right services providing a full stack framework
 */
$di = new FactoryDefault();

/**
 * Set config in controller
 */
$di->set('config', $config);

/**
 * The URL component is used to generate all kind of urls in the application
 */
$di->set('url', function () use ($config) {
    $url = new UrlResolver();
    $url->setBaseUri($config->application->baseUri);

    return $url;
}, true);

/**
 * Setting up the view component
 */
$di->set('view', function () use ($config) {

    $view = new View();

    $view->setViewsDir($config->application->viewsDir);

    $view->registerEngines(array(
        '.volt' => function ($view, $di) use ($config) {

            $volt = new VoltEngine($view, $di);

            $volt->setOptions(array(
                'compiledPath' => $config->application->cacheDir,
                'compiledSeparator' => '_'
            ));

            $compiler = $volt->getCompiler();
            $compiler->addFunction('time_in_ago', 'time_in_ago');
            $compiler->addFilter('time_in_ago', 'time_in_ago');
            $compiler->addFilter('time_ymd', 'time_ymd');
            $compiler->addFilter('get_cloudcdn_url', 'get_cloudcdn_url');
            $compiler->addFilter('get_cloudcdn_thumb_url', 'get_cloudcdn_thumb_url');
            $compiler->addFilter('get_cloudcdn_thumb_url', 'get_cloudcdn_thumb_url');
            $compiler->addFilter('get_image_labels', 'get_image_labels');

            return $volt;
        },
        '.phtml' => 'Phalcon\Mvc\View\Engine\Php'
    ));

    return $view;
}, true);

//Register the flash service with custom CSS classes
$di->set('flash', function() {
    $flash = new \Phalcon\Flash\Session(array(
        'error'   => 'alert alert-error',
        'success' => 'alert alert-success',
        'notice'  => 'alert alert-info',
    ));

    return $flash;
});

$di->set('profiler', function() {
    return new \Phalcon\Db\Profiler();
}, true);


/**
 * Database connection is created based in the parameters defined in the configuration file
 */
$di->set('db', function () use ($di) {
    $config = read_config('database');

    if ($config) {
        $connection = new Phalcon\Db\Adapter\Pdo\Mysql(array(
            'host'      => $config->database->host,
            'username'  => $config->database->username,
            'password'  => $config->database->password,
            'dbname'    => $config->database->dbname,
            'charset'   => 'utf8'
        ));

        if (is_dev()) { // 在开发环境下，开启记录 SQL 语句
            $eventsManager  = new Phalcon\Events\Manager();
            $profiler       = $di->getProfiler();

            $logger = new Phalcon\Logger\Adapter\File(LOG_DIR .'sql/'. date('Y-m-d') . '.log');
            //Listen all the database events
            $eventsManager->attach('db', function($event, $connection) use ($logger, $profiler) {
                //todo:可以考虑使用 $profiler->getProfilers() 在controller结束之后记录sql
                if ($event->getType() == 'beforeQuery') {
                    $profiler->startProfile($connection->getSQLStatement());
                }
                //一条语句查询结束，结束本次记录，记录结果会保存在profiler对象中
                if ($event->getType() == 'afterQuery') {
                    $profiler->stopProfile();
                    $execute_time = $profiler->getLastProfile()->getTotalElapsedSeconds();
                    $logger->log($execute_time." ".$connection->getSQLStatement(), Phalcon\Logger::INFO);
                }
            });

            //Assign the eventsManager to the db adapter instance
            $connection->setEventsManager($eventsManager);
        }

        return $connection;
    } else {
        die('不存在数据库配置文件 database.php。请将 apps/config/database.php.default 改为 database.php 并且配置好数据库连接');
    }
});

/**
 * Database connection is created based in the parameters defined in the configuration file
 */
$di->set('db_log', function () use ($di) {
    $config = read_config('database');
    if ($config) {
        $connection = new Phalcon\Db\Adapter\Pdo\Mysql(array(
            'host'      => $config->database_log->host,
            'username'  => $config->database_log->username,
            'password'  => $config->database_log->password,
            'dbname'    => $config->database_log->dbname,
            'charset'   => 'utf8'
        ));
        return $connection;
    } else {
        die('不存在数据库配置文件 database_log.php。请将 apps/config/database_log.php.default 改为 database.php 并且配置好数据库连接');
    }
});

/**
 * If the configuration specify the use of metadata adapter use it or use memory otherwise
 */
$di->set('modelsMetadata', function () {
    return new MetaDataAdapter();
});

/**
 * cookie
 */
$di->set('cookies', function() {
    $cookies = new Phalcon\Http\Response\Cookies();
    $cookies->useEncryption(false);

    return $cookies;
}, true);

/**
 * serucity component
 */
$di->set('security', function(){
    $security = new Phalcon\Security();
    //Set the password hashing factor to 12 rounds
    $security->setWorkFactor(15);

    return $security;
}, true);

/**
 * 加密模块
 */
$di->set('crypt', function() use ($config) {
    $crypt = new Phalcon\Crypt();
    $crypt->setKey($config->application->crypt_key);

    return $crypt;
}, true);

/**
 * 腾讯开放平台 api
 */
$di->set('txsdk', function() use ($config) {
    $txopenapi = new OpenApiV3($config->txsdk->appid, $config->txsdk->appkey);
    $txopenapi->setServerName($config->txsdk->server_name);

    return $txopenapi;
}, true);

/**
 * 图片文件存储，后期做成可切换
 */
$di->set('cloudCDN', function() use ($config) {
    //默认为七牛
    $cloud = new CloudCDN($config->qiniu->ak, $config->qiniu->sk,
                       $config->qiniu->bucket, $config->qiniu->domain);

    //允许接入又拍
    //$cloud = new Cloud($config->youpai->ak, $config->youpai->sk,
                       //$config->youpai->bucket, $config->youpai->domain);
    return $cloud;
}, true);

/**
 * 系统日志记录
 */
$di->set('sys_log', function() use ($config) {
    $logname    = $config->application->logDir . 'sys/' . date('Y-m-d') . '.log';
    $formatter  = new LineFormatter("[".date("Y-m-d H:i:s")."][API] - %message%");
    $logger     = new \Phalcon\Logger\Adapter\File($logname);
    $logger->setFormatter($formatter);

    return $logger;
}, true);

/**
 * 调试日志记录
 */
$di->set('debug_log', function() use ($config) {
    $logname    = $config->application->logDir . 'debug/' . date('Y-m-d') . '.log';

    return new \Phalcon\Logger\Adapter\File($logname);
}, true);

/**
 * 注册队列
 */
$di->set('queue', function() use ($config) {
    $config = read_config('database');

    //Connect to the queue
    $connection= new Phalcon\Queue\Beanstalk(array(
        'host' => $config->beanstalkd->host,
        'port' => $config->beanstalkd->port
    ));
/*
 * rabbitmq queue
    $connection = new \PhpAmqpLib\Connection\AMQPConnection(
        $config->rabbit->host,
        $config->rabbit->port,
        $config->rabbit->username,
        $config->rabbit->password
    );
*/
    return $connection;
});

/**
 * redis cache
 */
$di->set('cache', function() use ($config) {
     //* phalcon 2.0 support
    $config = read_config('database');
    $redis = new Redis();
    $redis->connect($config->redis->host, $config->redis->port);

    $frontend = new Phalcon\Cache\Frontend\Data(array(
        'lifetime' => EXPIRE_TIME
    ));

    $cache = new Phalcon\Cache\Backend\Redis($frontend, array(
        'redis' => $redis
    ));
    return $cache;
    /**
    $config = read_config('database');
    $redis = new Redis();
    $redis->connect($config->redis->host, $config->redis->port);
    return $redis;
     */
});

/**
 * Start the session the first time some component request the session service
 */
$di->set('session', function () {
    ini_set('session.name', 'token');
    //ini_set('session.save_handler', 'redis');
    //ini_set('session.save_path', 'tcp://localhost:6379');
    session_set_cookie_params(SESSION_EXPIRE);

    // 如果cookie中token是空的，不能用空值去替换sessionid
    if(isset($_COOKIE['token']) && $_COOKIE['token'] == ''){
        unset($_COOKIE['token']);
    }

    if(!class_exists('Redis')) {
        $session = new SessionAdapter();
    }
    else {
        $session = new Phalcon\Session\Adapter\Redis(array(
            'path' => "tcp://127.0.0.1:6379?weight=1",
            'name' => 'token'
        ));
    }
    @$session->start();

    return $session;
}, true);
