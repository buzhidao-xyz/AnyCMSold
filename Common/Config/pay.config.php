<?php
/**
 * 订单系统接口配置文件
 * 2014-12-03
 * imbzd
 */

//支付宝、微信回调HOST
define(PAY_NOTIFY_HOST,   'http://127.0.0.1/mpay/');

//接口列表
return array(
	//账号
	'APP_ID'              => '',
	//密码
	'APP_SECRET'          => '',
	//异步通知API
	'PAY_NOTIFY'          => array(
		'alipay' => PAY_NOTIFY_HOST.'alipaynotify',
		'wxpay'  => PAY_NOTIFY_HOST.'wxpaynotify',
	),
);