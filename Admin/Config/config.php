<?php
/**
 * 模块配置文件
 * wangbaoqing@imooly.com
 * 2015-05-15
 */
return array(
	//主题模板 - Smart
	'DEFAULT_THEME' => 'Smart',
	
	//系统初始化默认管理员(超级管理员super=1)
	//区别仅在系统内不可编辑和删除
	//manager表字段信息
	'SYSTEM_MANAGER' => array(
		'managerid' => 1,
		'account'   => 'admin',
	),

	//加载扩展配置文件 引用方式C('x.x')
	'LOAD_EXT_CONFIG' => array(
		//支付配置
		// 'PAY'   => 'pay.config',
	),
);