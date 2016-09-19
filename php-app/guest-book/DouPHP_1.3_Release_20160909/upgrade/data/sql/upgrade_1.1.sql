-------------------------------------------------------------------
-- 以下为升级至1.1版本
-------------------------------------------------------------------

-- 增加字段
ALTER TABLE `dou_product_category` ADD `parent_id` smallint(5) NOT NULL default '0' AFTER `description`;
ALTER TABLE `dou_article_category` ADD `parent_id` smallint(5) NOT NULL default '0' AFTER `description`;
ALTER TABLE `dou_article` ADD `image` varchar(255) NOT NULL default '' AFTER `content`;

-- 数据表增加
CREATE TABLE IF NOT EXISTS `dou_guestbook` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `title` varchar(150) NOT NULL default '',
  `name` varchar(60) NOT NULL default '',
  `contact_type` varchar(30) NOT NULL default '',
  `contact` varchar(150) NOT NULL default '',
  `content` text NOT NULL,
  `if_show` tinyint(1) NOT NULL default '0',
  `if_read` tinyint(1) NOT NULL default '0',
  `ip` varchar(15) NOT NULL default '',
  `add_time` int(10) unsigned NOT NULL default '0',
  `reply_id` smallint(5) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;