<?php
/**
 * 各服务SERVER
 * buzhdiao
 */

//解析SERVER
function hostpath()
{
    $hostpath = null;
    if (isset($_SERVER['REQUEST_URI'])) {
        $uripath = explode('?', $_SERVER['REQUEST_URI']);
        if (preg_match("/\.php$/", $uripath[0])) {
            $hostpathinfo = pathinfo($uripath[0]);
            $hostpath = $hostpathinfo['dirname'];
        } else if (preg_match("/\/$/", $uripath[0])) {
            $hostpath = substr($uripath[0], 0, strrpos($uripath[0],'/'));
        } else {
            $hostpath = $uripath[0];
        }
    } else {
        $hostpathinfo = pathinfo($_SERVER['PHP_SELF']);
        $hostpath = $hostpathinfo['dirname'];
    }

    $hostpath = defined('APP_INDEX') ? $hostpath.'/' : substr($hostpath, 0, strrpos($hostpath,'/')).'/';
    return stripslashes($hostpath);
}
defined('HOST_PATH') or define('HOST_PATH', hostpath());

$HOST = array(
    //服务器域名
    'HTTP_HOST'     => 'http://'.$_SERVER['HTTP_HOST'].'/',
    //系统 JS静态文件服务器
    'JS_SERVER'     => HOST_PATH.MODULE_NAME.'/',
    //系统 css静态文件服务器
    'CSS_SERVER'    => HOST_PATH.MODULE_NAME.'/',
    //系统 .svg .eot .ttf .woff字体文件服务器
    'FONT_SERVER'   => HOST_PATH.MODULE_NAME.'/',
    //系统 图片文件服务器
    'IMAGE_SERVER'  => HOST_PATH.MODULE_NAME.'/',
    //系统 声音文件服务器
    'SOUND_SERVER'  => HOST_PATH.MODULE_NAME.'/',
    //系统公共资源服务器
    'PUBLIC_SERVER' => HOST_PATH.'Public/',
);