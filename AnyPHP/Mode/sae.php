<?php   
/**
 * SAE应用模式定义文件
 */
return array(
    // 配置文件
    'config'    =>  array(
        ANY_PATH .'Config/config.php',   // 系统惯例配置
        CONF_PATH.'config'.CONF_EXT,      // 应用公共配置
        MODE_PATH.'Sae/config.php',//[sae] sae的惯例配置
    ),

    // 别名定义
    'alias'     =>  array(
        'Any\Log'               => CORE_PATH . 'Log'.EXT,
        'Any\Log\Driver\File'   => CORE_PATH . 'Log/Driver/File'.EXT,
        'Any\Exception'         => CORE_PATH . 'Exception'.EXT,
        'Any\Model'             => CORE_PATH . 'Model'.EXT,
        'Any\Db'                => CORE_PATH . 'Db'.EXT,
        'Any\Template'          => CORE_PATH . 'Template'.EXT,
        'Any\Cache'             => CORE_PATH . 'Cache'.EXT,
        'Any\Cache\Driver\File' => CORE_PATH . 'Cache/Driver/File'.EXT,
        'Any\Storage'           => CORE_PATH . 'Storage'.EXT,
    ),

    // 函数和类文件
    'core'      =>  array(
        ANY_PATH  . 'Common/functions.php',
        COMMON_PATH.'function.php',
        CORE_PATH . 'Hook'.EXT,
        CORE_PATH . 'App'.EXT,
        CORE_PATH . 'Dispatcher'.EXT,
        //CORE_PATH . 'Log'.EXT,
        CORE_PATH . 'Route'.EXT,
        CORE_PATH . 'Controller'.EXT,
        CORE_PATH . 'View'.EXT,
        BEHAVIOR_PATH . 'ParseTemplateBehavior'.EXT,
        BEHAVIOR_PATH . 'ContentReplaceBehavior'.EXT,
    ),
    // 行为扩展定义
    'tags'  =>  array(
        'app_begin'     =>  array(
            'Behavior\ReadHtmlCacheBehavior', // 读取静态缓存
        ),
        'app_end'       =>  array(
            'Behavior\ShowPageTraceBehavior', // 页面Trace显示
        ),
        'view_parse'    =>  array(
            'Behavior\ParseTemplateBehavior', // 模板解析 支持PHP、内置模板引擎和第三方模板引擎
        ),
        'template_filter'=> array(
            'Behavior\ContentReplaceBehavior', // 模板输出替换
        ),
        'view_filter'   =>  array(
            'Behavior\WriteHtmlCacheBehavior', // 写入静态缓存
        ),
    ),
);
