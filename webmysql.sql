/*
Navicat MySQL Data Transfer

Source Server         : 127
Source Server Version : 80015
Source Host           : localhost:3306
Source Database       : webmysql

Target Server Type    : MYSQL
Target Server Version : 80015
File Encoding         : 65001

Date: 2019-08-24 14:38:54
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for sysconfig
-- ----------------------------
DROP TABLE IF EXISTS `sysconfig`;
CREATE TABLE `sysconfig` (
  `id` int(11) NOT NULL,
  `accessKey` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `secretKey` varchar(255) DEFAULT NULL,
  `oneselfurl` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '己方回调地址',
  `serviceurl` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '币支付回调地址',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of sysconfig
-- ----------------------------
INSERT INTO `sysconfig` VALUES ('1', '3BDAF00B6F374A01A4DFBE32257F7E05', '1C1D365EEEAB40FD8E56901E27243DA0', 'http://172.16.10.117:7333/index', 'http://172.16.10.117:44385');

-- ----------------------------
-- Table structure for sys_users
-- ----------------------------
DROP TABLE IF EXISTS `sys_users`;
CREATE TABLE `sys_users` (
  `id` int(1) NOT NULL AUTO_INCREMENT,
  `username` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `password` char(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `quota` decimal(10,2) DEFAULT '0.00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of sys_users
-- ----------------------------
INSERT INTO `sys_users` VALUES ('1', 'admin', 'c4ca4238a0b923820dcc509a6f75849b', '70700.00');
INSERT INTO `sys_users` VALUES ('2', 'admin2', 'c4ca4238a0b923820dcc509a6f75849b', '0.00');
INSERT INTO `sys_users` VALUES ('3', 'admin3', 'c4ca4238a0b923820dcc509a6f75849b', '0.00');
INSERT INTO `sys_users` VALUES ('4', 'admin4', 'c4ca4238a0b923820dcc509a6f75849b', '0.00');

-- ----------------------------
-- Table structure for t_user_log
-- ----------------------------
DROP TABLE IF EXISTS `t_user_log`;
CREATE TABLE `t_user_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `quota` decimal(10,2) NOT NULL,
  `userid` int(11) NOT NULL,
  `createtime` datetime DEFAULT CURRENT_TIMESTAMP,
  `symbol` char(5) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `remark` char(50) DEFAULT NULL,
  `orderId` bigint(11) DEFAULT NULL COMMENT '订单ID',
  `confirm` int(11) DEFAULT NULL COMMENT '确认数',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of t_user_log
-- ----------------------------

-- ----------------------------
-- Table structure for t_user_log2
-- ----------------------------
DROP TABLE IF EXISTS `t_user_log2`;
CREATE TABLE `t_user_log2` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `quota` decimal(10,2) NOT NULL,
  `userid` int(11) NOT NULL,
  `createtime` datetime DEFAULT CURRENT_TIMESTAMP,
  `symbol` char(5) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `remark` char(50) DEFAULT NULL,
  `orderId` bigint(11) DEFAULT NULL,
  `confirm` int(11) DEFAULT NULL COMMENT '确认数',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of t_user_log2
-- ----------------------------
INSERT INTO `t_user_log2` VALUES ('26', '70700.00', '1', '2019-08-23 15:28:54', 'USDT', '1566545307', '1360520414233111', '1');
