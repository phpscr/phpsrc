-- phpMyAdmin SQL Dump
-- version 2.9.2
-- http://www.phpmyadmin.net
-- 
-- 主机: localhost
-- 生成日期: 2009 年 07 月 19 日 12:53
-- 服务器版本: 5.0.27
-- PHP 版本: 5.2.1
-- 
-- 数据库: `luo_book`
-- 

-- --------------------------------------------------------

-- 
-- 表的结构 `luo_booklist`
-- 

CREATE TABLE `luo_booklist` (
  `id` int(4) NOT NULL auto_increment,
  `name` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `content` text NOT NULL,
  `addtime` datetime NOT NULL,
  `replay` text NOT NULL,
  `replaytime` datetime NOT NULL,
  `state` int(1) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

-- 
-- 导出表中的数据 `luo_booklist`
-- 

INSERT INTO `luo_booklist` VALUES (4, 'asdf', 'asdf', 'asdf', '2009-07-17 21:50:21', 'aaaaa', '2009-07-17 21:56:06', 1);
INSERT INTO `luo_booklist` VALUES (2, 'bbb', 'bbb', 'bbb', '2009-07-17 21:29:08', 'asdf', '2009-07-17 21:36:01', 1);
INSERT INTO `luo_booklist` VALUES (3, 'Jerryluo', 'luoquanlu@qq.com', 'PHP留言板测试！', '2009-07-17 21:29:57', '貌似成功了', '2009-07-17 21:35:54', 1);
INSERT INTO `luo_booklist` VALUES (5, 'fffffffff', 'ffffff', 'fffffffffff', '2009-07-17 22:09:02', '', '2009-07-17 22:09:14', 1);
INSERT INTO `luo_booklist` VALUES (6, 'adsf', 'adsf', 'adsf', '2009-07-17 22:09:08', '', '2009-07-17 22:09:13', 1);

-- --------------------------------------------------------

-- 
-- 表的结构 `luo_manage`
-- 

CREATE TABLE `luo_manage` (
  `id` int(4) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- 导出表中的数据 `luo_manage`
-- 

INSERT INTO `luo_manage` VALUES (1, 'admin', '21232f297a57a5a743894a0e4a801fc3');
