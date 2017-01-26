<?php
require realpath(__DIR__ . '/../../apps/config/config.php'); // 加载配置及路径信息
require APPS_DIR . 'common.php';    // 导入函数库

$default_config =  array(
    'db' => array(
        'development' => array(
            'type'      => 'mysql',
            'host'      => 'localhost',
            'port'      => 3306,
            'database'  => 'psgod_new9',
            'user'      => 'root',
            'password'  => 'XiaoNongNv52',
            'charset'   => 'utf8',
            'directory' => 'clover',
        ),
        'production'  => array(
            'type'      => 'mysql',
            'host'      => 'localhost',
            'port'      => 3306,
            'database'  => 'psgod_new9',
            'user'      => 'root',
            'password'  => 'XiaoNongNv52',
            'charset'   => 'utf8',
            'directory' => 'clover',
        ),
    ),
    'migrations_dir'    => array('default' => APPS_DIR . 'migrations'),
    'db_dir'            => RUCKUSING_WORKING_BASE . DIRECTORY_SEPARATOR . 'db',
    'log_dir'           => APPS_DIR . 'tmp/migrations_log',
);

$config = read_config('database');

if ($config) {
    $env = (is_dev() ? 'development' : 'production');   // 根据是否开发环境切换不同的数据库连接
    $db_config_map = array(
        'dbname'    => 'database',
        'host'      => 'host',
        'password'  => 'password',
        'username'  => 'user',
        'socket'    => 'socket',
    );

    foreach ($db_config_map as $config_key => $rukus_key) {
        if (isset($config->$config_key)) {
            $default_config['db']["{$env}"]["{$rukus_key}"] = $config->$config_key;
        }
    }

    return $default_config;
} else {
    die('不存在数据库配置文件 database.php。请将 apps/config/database.php.default 改为 database.php 并且配置好数据库连接');
}
