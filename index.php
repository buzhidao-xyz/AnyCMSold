<?php
/**
 * AnyCMS [A PHP CMS]
 * 
 * luochuan.wang@gmail.com
 */

//应用入口文件

// 检测PHP环境
if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');

//开启缓冲Buffer 用ob_gzhandler回调来判断是否压缩输出gzip
ob_start('ob_gzhandler');

//开启调式模式
define('APP_DEBUG', True);

//绑定主入口模块 例：绑定Front模块 可以直接访问Front模块的controller/action
define('MAIN_MODULE', 'Admin');

//系统应用主入口标识
define('APP_INDEX', 1);

//加载AnyPHP主入口文件
require './AnyPHP/AnyPHP.php';

//冲刷缓冲Buffer 输出内容
ob_end_flush();