<?php
/**
 * 模块配置文件
 * wangbaoqing@imooly.com
 * 2015-05-15
 */
require_once('host.config.php');
require_once('db.config.php');

return array(
	'HOST' => $HOST,
	
	//默认语言
	'DEFAULT_LANG'  => 'zh-cn',
	//主题模板 - Default
	'DEFAULT_THEME' => 'Default',
	
	//数据库配置信息 支持多数据库配置
	'DB_CONFIG'     => $database,
	//mongodb配置信息
	'MONGO'         => $mongo,

	//系统初始化默认管理员(超级管理员super=1)
	//区别仅在系统内不可编辑和删除
	//manager表字段信息
	'SYSTEM_MANAGER' => array(
		'managerid' => 1,
		'account'   => 'admin',
	),

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