<?php
/**
 * AnyPHP API模式定义
 */
return array(
    // 配置文件
    'config'    =>  array(
        ANY_PATH .'Config/config.php',   // 系统惯例配置
        CONF_PATH.'config'.CONF_EXT,      // 应用公共配置
    ),

    // 别名定义
    'alias'     =>  array(
        'Any\Exception'         => CORE_PATH . 'Exception'.EXT,
        'Any\Model'             => CORE_PATH . 'Model'.EXT,
        'Any\Db'                => CORE_PATH . 'Db'.EXT,
        'Any\Cache'             => CORE_PATH . 'Cache'.EXT,
        'Any\Cache\Driver\File' => CORE_PATH . 'Cache/Driver/File'.EXT,
        'Any\Storage'           => CORE_PATH . 'Storage'.EXT,
    ),

    // 函数和类文件
    'core'      =>  array(
        MODE_PATH.'Api/functions.php',
        COMMON_PATH.'function.php',
        MODE_PATH . 'Api/App'.EXT,
        MODE_PATH . 'Api/Dispatcher'.EXT,
        MODE_PATH . 'Api/Controller'.EXT,
        CORE_PATH . 'Behavior'.EXT,
    ),
    // 行为扩展定义
    'tags'  =>  array(
    ),
);
