<?php
/**
 * 模块配置文件
 * wangbaoqing@imooly.com
 * 2014-07-15
 */
require_once('db.config.php');

return array(
	//模板引擎要自动替换的字符串，必须是数组形式
	'TMPL_PARSE_STRING'  => array(
		'__UPLOAD__'     =>  __ROOT__.'/Upload/',
	),

	//服务器域名
	'HTTP_HOST'          => 'http://'.$_SERVER['HTTP_HOST'],
	//系统 JS静态文件服务器
	'JS_FILE_SERVER'     => null,
	//系统 css静态文件服务器
	'CSS_FILE_SERVER'    => null,
	//系统 .svg .eot .ttf .woff字体文件服务器
	'FONT_FILE_SERVER'   => null,
	//系统 图片文件服务器
	'IMAGE_SERVER'       => null,

	//开启多语言切换
	'LANG_SWITCH_ON'     => true,
	//默认语言
	'LANG_DEFAULT'       => 'zh-cn',

	/**
	 * 数据库配置信息
	 * 支持多数据库配置
	 */
	'DB_CONFIG' => $database,

	//mongodb配置信息
	'MONGO'     => $mongo,

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
		'PAY'   => 'pay.config',
	),
);