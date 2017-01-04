-------------------------------------------------------------------
-- 以下为升级至1.0版本
-------------------------------------------------------------------

-- 修改表名
RENAME TABLE `dou_ad` TO `dou_show`;

-- 字段属性修正
ALTER TABLE `dou_show` CHANGE `sort` `sort` tinyint(1) unsigned NOT NULL default '50';
ALTER TABLE `dou_admin` CHANGE `action_list` `action_list` text NOT NULL;
ALTER TABLE `dou_article` CHANGE `keywords` `keywords` varchar(255) NOT NULL default '';
ALTER TABLE `dou_article` CHANGE `description` `description` varchar(255) NOT NULL default '';
ALTER TABLE `dou_article_category` CHANGE `description` `description` varchar(255) NOT NULL default '';
ALTER TABLE `dou_article_category` CHANGE `sort` `sort` tinyint(1) unsigned NOT NULL default '50';
ALTER TABLE `dou_config` CHANGE `type` `type` varchar(10) NOT NULL default '';
ALTER TABLE `dou_link` CHANGE `sort` `sort` tinyint(1) unsigned NOT NULL default '50';
ALTER TABLE `dou_nav` CHANGE `sort` `sort` tinyint(1) unsigned NOT NULL default '50';
ALTER TABLE `dou_page` CHANGE `keywords` `keywords` varchar(255) NOT NULL default '';
ALTER TABLE `dou_page` CHANGE `description` `description` varchar(255) NOT NULL default '';
ALTER TABLE `dou_product` CHANGE `keywords` `keywords` varchar(255) NOT NULL default '';
ALTER TABLE `dou_product` CHANGE `description` `description` varchar(255) NOT NULL default '';
ALTER TABLE `dou_product_category` CHANGE `description` `description` varchar(255) NOT NULL default '';
ALTER TABLE `dou_product_category` CHANGE `sort` `sort` tinyint(1) unsigned NOT NULL default '50';

-- 字段名称修改
ALTER TABLE `dou_article_category` CHANGE `cat_desc` `description` varchar(255) NOT NULL default '';
ALTER TABLE `dou_product_category` CHANGE `cat_desc` `description` varchar(255) NOT NULL default '';
ALTER TABLE `dou_nav` CHANGE `nav_type` `module` varchar(20) NOT NULL;
ALTER TABLE `dou_show` CHANGE `ad_name` `show_name` varchar(60) NOT NULL default '';
ALTER TABLE `dou_show` CHANGE `ad_link` `show_link` varchar(255) NOT NULL default '';
ALTER TABLE `dou_show` CHANGE `ad_img` `show_img` varchar(255) NOT NULL;

-- 增加字段
ALTER TABLE `dou_nav` ADD `type` varchar(10) NOT NULL AFTER `parent_id`;
ALTER TABLE `dou_product` ADD `defined` text NOT NULL AFTER `price`;
ALTER TABLE `dou_article` ADD `defined` text NOT NULL AFTER `title`;
ALTER TABLE `dou_article` ADD `home_sort` varchar(2) NOT NULL default '' AFTER `description`;
ALTER TABLE `dou_product` ADD `home_sort` varchar(2) NOT NULL default '' AFTER `description`;

-- 数据修改
UPDATE `dou_show` SET ad_img=REPLACE(ad_img,'data/adimg','data/slide');
UPDATE `dou_nav` SET `type` = 'middle' WHERE `type` = '';