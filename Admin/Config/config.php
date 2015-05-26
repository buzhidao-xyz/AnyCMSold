<?php
/**
 * 模块配置文件
 * wangbaoqing@imooly.com
 * 2015-05-15
 */
require_once('serverhost.config.php');
require_once('db.config.php');

return array(
	'SERVER_HOST' => $SERVER_HOST,
	
	//默认语言
	'DEFAULT_LANG'  => 'zh-cn',
	//默认主题模板
	'DEFAULT_THEME' => 'Default',
	
	//数据库配置信息 支持多数据库配置
	'DB_CONFIG'     => $database,
	//mongodb配置信息
	'MONGO'         => $mongo,

	//SESSION配置信息
	'SESSION_TYPE'       => '',
	'SESSION_PREFIX'     => 'AnyCMS',
	'VAR_SESSION_ID'     => 'sessionid',
	'SESSION_OPTIONS'    => array(
		'name'   => 'AnyCMS',
		'expire' => 7200 //session默认过期时间 2小时=7200秒
	),

	//加载扩展配置文件 引用方式C('x.x')
	'LOAD_EXT_CONFIG' => array(
		//支付配置
		// 'PAY'   => 'pay.config',
	),
);