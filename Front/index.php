<?php
/**
 * AnyCMS [A PHP CMS]
 * 
 * luochuan.wang@gmail.com
 */

//应用模块入口文件

// 检测PHP环境
if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');

//开启缓冲Buffer 用ob_gzhandler回调来判断是否压缩输出gzip
ob_start('ob_gzhandler');

//开启调式模式
define('APP_DEBUG', True);

//绑定模块
define('MODULE_NAME', 'Front');

//应用模块主入口标识
define('MODULE_INDEX', 1);

//加载AnyPHP主入口文件
require '../AnyPHP/AnyPHP.php';

//冲刷缓冲Buffer 输出内容
ob_end_flush();