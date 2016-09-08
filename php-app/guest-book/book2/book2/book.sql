
SET FOREIGN_KEY_CHECKS=0;
-- ----------------------------
-- Table structure for `admin`
-- ----------------------------
DROP TABLE IF EXISTS `admin`;
CREATE TABLE `admin` (
  `id` int(10) NOT NULL auto_increment,
  `username` varchar(50) default NULL,
  `password` varchar(50) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=gb2312;

-- ----------------------------
-- Records of admin
-- ----------------------------
INSERT INTO admin VALUES ('1', 'admin', 'admin');

-- ----------------------------
-- Table structure for `leavewords`
-- ----------------------------
DROP TABLE IF EXISTS `leavewords`;
CREATE TABLE `leavewords` (
  `id` int(10) NOT NULL auto_increment,
  `username` varchar(50) default NULL,
  `qq` int(10) default NULL,
  `email` varchar(50) default NULL,
  `homepage` varchar(50) default NULL,
  `face` varchar(50) default NULL,
  `leave_title` varchar(50) default NULL,
  `leave_contents` text,
  `leave_time` datetime default NULL,
  `ip` varchar(20) NOT NULL default '',
  `is_audit` int(11) default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=gb2312;

-- ----------------------------
-- Records of leavewords
-- ----------------------------
INSERT INTO leavewords VALUES ('1', 'EIMS', '332003288', '332003288@qq.com', 'http://www.eims.org.cn', '1', 'EIMS Title', 'CONTENT', '2014-10-10 22:01:00', '127.0.0.1', '1');
INSERT INTO leavewords VALUES ('2', 'E2', '332003288', '332003288@qq.com', 'http://www.eims.org.cn', '6', '测试PHP留言本', 'C！', '2014-10-10 22:01:00', '127.0.0.1', '1');


-- ----------------------------
-- Table structure for `lockip`
-- ----------------------------
DROP TABLE IF EXISTS `lockip`;
CREATE TABLE `lockip` (
  `Id` int(11) NOT NULL auto_increment,
  `lockip` varchar(20) NOT NULL default '',
  PRIMARY KEY  (`Id`)
) ENGINE=MyISAM DEFAULT CHARSET=gb2312;

-- ----------------------------
-- Records of lockip
-- ----------------------------
INSERT INTO lockip VALUES ('10', '');

-- ----------------------------
-- Table structure for `reply`
-- ----------------------------
DROP TABLE IF EXISTS `reply`;
CREATE TABLE `reply` (
  `id` int(10) NOT NULL auto_increment,
  `leaveid` int(10) default NULL,
  `leaveuser` varchar(10) default NULL,
  `reply_contents` text,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=gb2312;

-- ----------------------------
-- Records of reply
-- ----------------------------
INSERT INTO reply VALUES ('1', '1', '管理员', '回复');
INSERT INTO reply VALUES ('2', '2', '管理员', 'Reply');

-- ----------------------------
-- Table structure for `system`
-- ----------------------------
DROP TABLE IF EXISTS `system`;
CREATE TABLE `system` (
  `name` varchar(225) default NULL COMMENT '网站名称',
  `title` varchar(225) default NULL COMMENT '标题',
  `keywords` varchar(225) default NULL COMMENT '关键字',
  `smalltext` varchar(225) default NULL COMMENT 'smalltext',
  `url` varchar(80) default NULL COMMENT '网站地址',
  `page` int(11) default '5',
  `is_audit` int(11) default '0',
  `is_html` int(11) default '0',
  `copyright` text COMMENT '版权信息'
) ENGINE=MyISAM DEFAULT CHARSET=gb2312;

-- ----------------------------
-- Records of system
-- ----------------------------
INSERT INTO system VALUES ('简易留言本', '简易留言本', '留言本', '留言本', 'http://www.eims.org.cn', '5', '1', '1', 'Copyright @ ALL Rights Reserved');
