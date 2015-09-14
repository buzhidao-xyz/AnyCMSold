/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50528
Source Host           : 127.0.0.1:3306
Source Database       : anycms

Target Server Type    : MYSQL
Target Server Version : 50528
File Encoding         : 65001

Date: 2015-09-14 22:11:00
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for any_manager
-- ----------------------------
DROP TABLE IF EXISTS `any_manager`;
CREATE TABLE `any_manager` (
  `managerid` int(10) NOT NULL AUTO_INCREMENT COMMENT '管理员id 自增+1',
  `account` varchar(20) NOT NULL COMMENT '账户',
  `password` varchar(50) NOT NULL COMMENT '密码 md5(md5(password)+mkey)',
  `mkey` char(6) NOT NULL COMMENT '加密字符串',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '账户状态 0禁用 1启用 默认1',
  `super` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否超级管理员 0否 1是 默认0',
  `createtime` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatetime` int(10) DEFAULT '0' COMMENT '更新时间',
  `createip` bigint(15) DEFAULT '0' COMMENT '创建人IP',
  `lastlogintime` int(10) DEFAULT '0' COMMENT '上次登录时间',
  `lastloginip` bigint(15) DEFAULT '0' COMMENT '上次登录IP',
  `logincount` int(10) DEFAULT '0' COMMENT '登录次数',
  `isdelete` tinyint(1) DEFAULT '0' COMMENT '是否已删除 0否 1是',
  PRIMARY KEY (`managerid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of any_manager
-- ----------------------------
INSERT INTO `any_manager` VALUES ('1', 'admin', '4183cde54a889bb846326fd53f10aebc', 'kj6u9', '1', '1', '1437235149', '1442034587', '2130706433', '1442034587', '0', '103', '0');
INSERT INTO `any_manager` VALUES ('2', 'test0', '4183cde54a889bb846326fd53f10aebc', 'kj6u9', '1', '0', '1437235149', '1439036789', '2130706433', '1439036789', '2130706433', '1', '0');
INSERT INTO `any_manager` VALUES ('3', 'test1', '4183cde54a889bb846326fd53f10aebc', 'kj6u9', '1', '0', '1437235149', '0', '0', '0', '0', '0', '0');

-- ----------------------------
-- Table structure for any_manager_loginlog
-- ----------------------------
DROP TABLE IF EXISTS `any_manager_loginlog`;
CREATE TABLE `any_manager_loginlog` (
  `logid` int(10) NOT NULL AUTO_INCREMENT COMMENT '日志id',
  `managerid` int(10) NOT NULL DEFAULT '0' COMMENT '管理员id',
  `account` varchar(50) NOT NULL COMMENT '登录账户',
  `logintime` int(10) NOT NULL DEFAULT '0' COMMENT '登录时间',
  `loginip` bigint(15) DEFAULT '0' COMMENT '登录IP',
  `result` tinyint(1) NOT NULL DEFAULT '1' COMMENT '结果 0登录失败 1登录成功',
  `browser` varchar(500) NOT NULL COMMENT '浏览器信息',
  `resume` varchar(500) DEFAULT NULL COMMENT '备注信息',
  PRIMARY KEY (`logid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of any_manager_loginlog
-- ----------------------------

-- ----------------------------
-- Table structure for any_manager_oplog
-- ----------------------------
DROP TABLE IF EXISTS `any_manager_oplog`;
CREATE TABLE `any_manager_oplog` (
  `logid` int(10) NOT NULL AUTO_INCREMENT COMMENT '日志id',
  `managerid` int(10) NOT NULL COMMENT '管理员id',
  `userid` int(10) DEFAULT NULL COMMENT '该管理员账户关联的员工id',
  `username` varchar(50) DEFAULT NULL COMMENT '员工名称',
  `optime` int(10) NOT NULL DEFAULT '0' COMMENT '操作时间',
  `opip` bigint(15) DEFAULT '0' COMMENT '客户端IP',
  `logcontent` varchar(500) DEFAULT NULL COMMENT '操作内容',
  PRIMARY KEY (`logid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of any_manager_oplog
-- ----------------------------

-- ----------------------------
-- Table structure for any_manager_role
-- ----------------------------
DROP TABLE IF EXISTS `any_manager_role`;
CREATE TABLE `any_manager_role` (
  `managerid` int(10) NOT NULL DEFAULT '0' COMMENT '管理员id',
  `roleid` int(10) NOT NULL DEFAULT '0' COMMENT '角色id'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of any_manager_role
-- ----------------------------
INSERT INTO `any_manager_role` VALUES ('2', '1');
INSERT INTO `any_manager_role` VALUES ('2', '2');

-- ----------------------------
-- Table structure for any_menu_group
-- ----------------------------
DROP TABLE IF EXISTS `any_menu_group`;
CREATE TABLE `any_menu_group` (
  `groupid` int(10) NOT NULL AUTO_INCREMENT,
  `groupname` varchar(50) NOT NULL COMMENT '组菜单名称',
  `control` varchar(50) NOT NULL COMMENT '控制器',
  `action` varchar(50) NOT NULL COMMENT '动作',
  `show` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否显示 0否 1是',
  `icon` varchar(50) DEFAULT NULL COMMENT 'icon font',
  `createtime` int(10) NOT NULL DEFAULT '0',
  `updatetime` int(10) DEFAULT '0',
  PRIMARY KEY (`groupid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of any_menu_group
-- ----------------------------
INSERT INTO `any_menu_group` VALUES ('1', '管理中心', 'Index', 'dashboard', '1', 'fa-home', '1437235149', '0');
INSERT INTO `any_menu_group` VALUES ('2', '系统管理', 'System', 'index', '1', 'fa-gear', '1437235149', '0');

-- ----------------------------
-- Table structure for any_menu_node
-- ----------------------------
DROP TABLE IF EXISTS `any_menu_node`;
CREATE TABLE `any_menu_node` (
  `nodeid` int(10) NOT NULL AUTO_INCREMENT,
  `nodename` varchar(50) NOT NULL COMMENT '节点菜单名称',
  `control` varchar(50) NOT NULL COMMENT '控制器',
  `action` varchar(50) NOT NULL COMMENT '动作',
  `pnodeid` int(10) NOT NULL DEFAULT '0' COMMENT '父节点id',
  `groupid` int(10) NOT NULL DEFAULT '0' COMMENT '组菜单id',
  `show` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否显示 0否 1是',
  `createtime` int(10) NOT NULL DEFAULT '0',
  `updatetime` int(10) DEFAULT '0',
  PRIMARY KEY (`nodeid`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of any_menu_node
-- ----------------------------
INSERT INTO `any_menu_node` VALUES ('1', '管理员', 'Manager', 'index', '0', '2', '1', '1437235149', '0');
INSERT INTO `any_menu_node` VALUES ('2', '角色管理', 'Role', 'index', '0', '2', '1', '1437235149', '0');
INSERT INTO `any_menu_node` VALUES ('3', '菜单管理', 'Menu', 'index', '0', '2', '1', '1437235149', '0');
INSERT INTO `any_menu_node` VALUES ('4', '组菜单', 'Menu', 'group', '3', '0', '1', '1437235149', '0');
INSERT INTO `any_menu_node` VALUES ('5', '节点菜单', 'Menu', 'node', '3', '0', '1', '1437235149', '0');
INSERT INTO `any_menu_node` VALUES ('6', '日志管理', 'Manager', 'log', '0', '2', '1', '1437235149', '0');
INSERT INTO `any_menu_node` VALUES ('7', '管理员登录日志', 'Manager', 'loginlog', '6', '0', '1', '1437235149', '0');
INSERT INTO `any_menu_node` VALUES ('8', '管理员操作日志', 'Manager', 'operatelog', '6', '0', '1', '1437235149', '0');

-- ----------------------------
-- Table structure for any_role
-- ----------------------------
DROP TABLE IF EXISTS `any_role`;
CREATE TABLE `any_role` (
  `roleid` int(10) NOT NULL AUTO_INCREMENT,
  `rolename` varchar(50) NOT NULL COMMENT '角色名称',
  `createtime` int(10) NOT NULL DEFAULT '0',
  `updatetime` int(10) DEFAULT '0',
  PRIMARY KEY (`roleid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of any_role
-- ----------------------------
INSERT INTO `any_role` VALUES ('1', '测试管理员', '0', '0');
INSERT INTO `any_role` VALUES ('2', '系统管理员', '0', '0');

-- ----------------------------
-- Table structure for any_role_node
-- ----------------------------
DROP TABLE IF EXISTS `any_role_node`;
CREATE TABLE `any_role_node` (
  `roleid` int(10) NOT NULL,
  `groupid` int(10) NOT NULL DEFAULT '0' COMMENT '组菜单id',
  `nodeid` int(10) NOT NULL DEFAULT '0' COMMENT '节点菜单id'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of any_role_node
-- ----------------------------
INSERT INTO `any_role_node` VALUES ('1', '1', '0');
INSERT INTO `any_role_node` VALUES ('1', '2', '1');
INSERT INTO `any_role_node` VALUES ('1', '2', '2');
INSERT INTO `any_role_node` VALUES ('1', '2', '3');
INSERT INTO `any_role_node` VALUES ('1', '0', '4');
INSERT INTO `any_role_node` VALUES ('1', '0', '5');
INSERT INTO `any_role_node` VALUES ('2', '2', '6');
INSERT INTO `any_role_node` VALUES ('2', '0', '7');
INSERT INTO `any_role_node` VALUES ('2', '0', '8');

-- ----------------------------
-- Table structure for any_system_config
-- ----------------------------
DROP TABLE IF EXISTS `any_system_config`;
CREATE TABLE `any_system_config` (
  `cfgid` int(10) NOT NULL AUTO_INCREMENT COMMENT '主键 自增+1',
  `cfgtype` varchar(50) NOT NULL COMMENT '参数类型 int|float|string|text|json',
  `cfgname` varchar(100) NOT NULL COMMENT '参数名称',
  `cfgkey` varchar(50) NOT NULL COMMENT '参数key',
  `cfgvalue` varchar(500) NOT NULL COMMENT '参数值',
  `cfggroup` varchar(50) NOT NULL COMMENT '所属分组',
  `createtime` int(10) NOT NULL DEFAULT '0' COMMENT '添加时间',
  `updatetime` int(10) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`cfgid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of any_system_config
-- ----------------------------
