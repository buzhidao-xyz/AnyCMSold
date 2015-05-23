<?php
/**
 * AnyPHP公共入口文件
 * 2015-04-26
 * 250175411@qq.com
 */

// 记录开始运行时间
$GLOBALS['_beginTime'] = microtime(TRUE);
// 记录内存初始使用
define('MEMORY_LIMIT_ON',function_exists('memory_get_usage'));
if(MEMORY_LIMIT_ON) $GLOBALS['_startUseMems'] = memory_get_usage();

// 版本信息
const ANY_VERSION     =   '1.0';

// URL 模式定义
const URL_COMMON        =   0;  //普通模式
const URL_PATHINFO      =   1;  //PATHINFO模式
const URL_REWRITE       =   2;  //REWRITE模式
const URL_COMPAT        =   3;  // 兼容模式

// 类文件后缀
const EXT               =   '.class.php'; 

// 系统常量定义
defined('ANY_DIR')      or define('ANY_DIR',        str_replace('\\','/',__DIR__));
defined('CMS_PATH')     or define('CMS_PATH',       substr(ANY_DIR, 0, strrpos(ANY_DIR,'/')).'/');
defined('ANY_PATH')     or define('ANY_PATH',       ANY_DIR.'/');
defined('APP_PATH')     or define('APP_PATH',       substr(ANY_DIR, 0, strrpos(ANY_DIR,'/')).'/');
defined('APP_STATUS')   or define('APP_STATUS',     ''); // 应用状态 加载对应的配置文件
defined('APP_DEBUG')    or define('APP_DEBUG',      false); // 是否调试模式

defined('MAIN_MODULE')  or define('MAIN_MODULE',    MODULE_NAME);
defined('APP_MODULE')   or define('APP_MODULE',     defined('MAIN_MODULE') ? MAIN_MODULE : (defined('MODULE_NAME')?MODULE_NAME:''));

defined('TIMESTAMP')    or define('TIMESTAMP', time());

if(function_exists('saeAutoLoader')){// 自动识别SAE环境
    defined('APP_MODE')     or define('APP_MODE',      'sae');
    defined('STORAGE_TYPE') or define('STORAGE_TYPE',  'Sae');
}else{
    defined('APP_MODE')     or define('APP_MODE',       'common'); // 应用模式 默认为普通模式    
    defined('STORAGE_TYPE') or define('STORAGE_TYPE',   'File'); // 存储类型 默认为File    
}

defined('RUNTIME_PATH') or define('RUNTIME_PATH',   CMS_PATH.'Runtime/');   // 系统运行时目录
defined('PUBLIC_PATH')  or define('PUBLIC_PATH',    CMS_PATH.'Public/');    // 系統公共静态文件目录
defined('UPLOAD_PATH')  or define('UPLOAD_PATH',    CMS_PATH.'Upload/');    // 系统上传目录
defined('LIB_PATH')     or define('LIB_PATH',       realpath(ANY_PATH.'Library').'/'); // 系统核心类库目录
defined('CORE_PATH')    or define('CORE_PATH',      LIB_PATH.'Any/'); // Any类库目录
defined('BEHAVIOR_PATH')or define('BEHAVIOR_PATH',  LIB_PATH.'Behavior/'); // 行为类库目录
defined('MODE_PATH')    or define('MODE_PATH',      ANY_PATH.'Mode/'); // 系统应用模式目录
defined('VENDOR_PATH')  or define('VENDOR_PATH',    LIB_PATH.'Vendor/'); // 第三方类库目录
defined('COMMON_PATH')  or define('COMMON_PATH',    APP_PATH.APP_MODULE.'/Common/'); // 应用公共目录
defined('CONF_PATH')    or define('CONF_PATH',      APP_PATH.APP_MODULE.'/Config/'); // 应用配置目录
defined('LANG_PATH')    or define('LANG_PATH',      COMMON_PATH.'Lang/'); // 应用语言目录
defined('HTML_PATH')    or define('HTML_PATH',      APP_PATH.APP_MODULE.'/Html/'); // 应用静态目录
defined('LOG_PATH')     or define('LOG_PATH',       RUNTIME_PATH.'Logs/'); // 应用日志目录
defined('COMPILE_PATH') or define('COMPILE_PATH',   RUNTIME_PATH.'Compile/'); // 应用模板编译目录
defined('CACHE_PATH')   or define('CACHE_PATH',     RUNTIME_PATH.'Cache/'); // 应用文件缓存目录
defined('DATA_PATH')    or define('DATA_PATH',      RUNTIME_PATH.'Data/'); // 应用数据目录
defined('CONF_EXT')     or define('CONF_EXT',       '.php'); // 配置文件后缀
defined('CONF_PARSE')   or define('CONF_PARSE',     '');    // 配置文件解析方法
defined('ADDON_PATH')   or define('ADDON_PATH',     APP_PATH.APP_MODULE.'/Addon');

// 系统信息
define('IS_CGI',(0 === strpos(PHP_SAPI,'cgi') || false !== strpos(PHP_SAPI,'fcgi')) ? 1 : 0 );
define('IS_WIN',strstr(PHP_OS, 'WIN') ? 1 : 0 );
define('IS_CLI',PHP_SAPI=='cli'? 1   :   0);

if(!IS_CLI) {
    // 当前文件名
    if(!defined('_PHP_FILE_')) {
        if(IS_CGI) {
            //CGI/FASTCGI模式下
            $_temp  = explode('.php',$_SERVER['PHP_SELF']);
            define('_PHP_FILE_',    rtrim(str_replace($_SERVER['HTTP_HOST'],'',$_temp[0].'.php'),'/'));
        }else {
            define('_PHP_FILE_',    rtrim($_SERVER['SCRIPT_NAME'],'/'));
        }
    }
    if(!defined('__ROOT__')) {
        $_root  =   rtrim(dirname(_PHP_FILE_),'/');
        define('__ROOT__',  (($_root=='/' || $_root=='\\')?'':$_root));
    }
}

// 加载核心Any类
require CORE_PATH.'Any'.EXT;
// 应用初始化
Any\Any::start();