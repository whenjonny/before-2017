<?php

date_default_timezone_set('Asia/Shanghai'); //时区

setlocale(LC_ALL, 'zh_CN.utf-8');   //字符集

define('DEV', false);    // 开发环境开关

if (function_exists('ini_set')) {
	if (defined('DEV') && DEV) {
		error_reporting(E_ALL);
		ini_set('display_errors', 'On');
	} else {
	    ini_set('display_errors', 'Off');
	}
}
ini_set('display_errors', 'On');

/**
 * 路径分隔符
 */
define('__DS__',  DIRECTORY_SEPARATOR);

/**
 * 根目录
 */
define('__ROOT__', realpath(__DIR__ . '/../../') . __DS__);

/**
 * apps 目录
 */
define('APPS_DIR', __ROOT__ . 'apps' . __DS__);

/**
 * modules 目录
 */
define('MODULES_DIR', APPS_DIR . 'modules' . __DS__);

/**
 * 配置文件目录
 */
define('CONFIG_DIR', APPS_DIR . 'config' . __DS__);

/**
 * 库目录
 */
define('LIB_DIR', APPS_DIR . 'library' . __DS__);

/**
 * 日志文件目录
 */
define('LOG_DIR', APPS_DIR . 'tmp' . __DS__ . 'logs' . __DS__);

/**
 * 登陆过期时间(30d)
 */
define('SESSION_EXPIRE', 30/*days*/ * 24/*hours*/ * 60/*minutes*/ * 60/*seconds*/);

/**
 * 审核过期时间(30min)
 */
define('VERIFY_EXPIRE', 0.5/*hours*/ * 60/*minutes*/ * 60/*seconds*/);

/**
 * 心跳包时间(2min)
 */
define('HEARTBEAT_EXPIRE', 2/*minutes*/ * 60/*seconds*/);

/**
 * 操作过期时间
 * todo: 设置为可以控制
 */
define('EXPIRE_TIME', 24/*hours*/ * 60/*minutes*/ * 60/*seconds*/);

//是否开启登陆验证（判断用户是否登陆）
define('CHECK_LOGIN', TRUE);

//是否开启权限判断（角色对应的）
define('CHECK_PERMISSIONS', TRUE);


/** 友盟相关 **/
//友盟APPKEY  MASTER SECRET
define('UMENG_IOS_APPKEY', '5545afea67e58eb5d7001cd3');
define('UMENG_IOS_MASTER_SECRET','sdbmz0djxqf0jaiupfdtxmf2y0wggcsl');

define('UMENG_ANDROID_APPKEY', '5534c256e0f55aa48c002909');
define('UMENG_ANDROID_MASTER_SECRET','0s1phi0ghw5wbmik38khols1xbsjwzan');

//友盟SECRET
define('UMENG_SECRET','c8f974673fbd1188aa00218f7d3cbac5');


//玄武短信发送平台 用户名和密码
define('XW_USERNAME', 'szyww@szyww');
define('XW_PASSWORD', 'xw4024');

//微信AppKEY
define('WX_APPID', 'wx86ff6f67a2b9b4b8');
define('WX_APPSECRET', 'c2da31fda3acf1c09c40ee25772b6ca5');

define('VERIFY_MSG', '您好！您在求PS大神的验证码为：::code::。');

define('APP_NAME', '求PS大神');

