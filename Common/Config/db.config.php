<?php
/**
 * 数据库配置文件
 * 2015-05-03
 * imbzd
 */

//默认数据库配置
$database = array(
	//默认配置
	'DEFAULT_CONFIG' => array(
		// 数据库类型
		'DB_TYPE'            => 'mysql',
		// 数据库HOST
		'DB_HOST'            => '127.0.0.1',
		// 数据库端口
		'DB_PORT'            => 3306,
		// 数据库名
		'DB_NAME'            => 'anycms',
		// 用户名
		'DB_USER'            => 'root',
		// 密码
		'DB_PWD'             => '123456',
		// 表前缀
		'DB_PREFIX'          => 'any_',
		// 字符集
		'DB_CHARSET'         => 'utf8',
		// 字段名小写
		'DB_CASE_LOWER'      => false,
	),
	//第二个数据库配置
	'NEW_CONFIG' => array(
		// 数据库类型
		'DB_TYPE'            => 'mysql',
		// 数据库HOST
		'DB_HOST'            => '127.0.0.1',
		// 数据库端口
		'DB_PORT'            => 3306,
		// 数据库名
		'DB_NAME'            => 'anycms',
		// 用户名
		'DB_USER'            => 'root',
		// 密码
		'DB_PWD'             => '123456',
		// 表前缀
		'DB_PREFIX'          => 'any_',
		// 字符集
		'DB_CHARSET'         => 'utf8',
		// 字段名小写
		'DB_CASE_LOWER'      => false,
	),
);

//mongodb配置
$mongo = array(
	//默认
	'DEFAULT_CONFIG' => array(
		'username' => '',
		'password' => '',
		'hostname' => '127.0.0.1', //服务器地址 例：host1,host2,host3
		'hostport' => '27017', //服务器端口 例：27017,27017,27017
		'database' => 'anycms',
		'options'   => array(
			'replicaSet' => '', //如果是复本集模式，此处填写复本集名称
		)
	),
	//第二个库
	'NEW_CONFIG' => array(
		'username' => '',
		'password' => '',
		'hostname' => '127.0.0.1', //服务器地址 例：host1,host2,host3
		'hostport' => '27017', //服务器端口 例：27017,27017,27017
		'database' => 'anycms',
		'options'   => array(
			'replicaSet' => '', //如果是复本集模式，此处填写复本集名称
		)
	),
);