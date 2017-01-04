-------------------------------------------------------------------
-- 以下为升级至1.3版本
-------------------------------------------------------------------

-- 字段名称修改
ALTER TABLE `dou_product` CHANGE `product_name` `name` varchar(150) NOT NULL default '';
ALTER TABLE `dou_product` CHANGE `product_image` `image` varchar(255) NOT NULL default '';
ALTER TABLE `dou_article` CHANGE `home_sort` `sort` tinyint(1) unsigned NOT NULL default '0';
ALTER TABLE `dou_product` CHANGE `home_sort` `sort` tinyint(1) unsigned NOT NULL default '0';

-- 新增系统设置
DROP TABLE IF EXISTS dou_config;
CREATE TABLE `dou_config` (
  `name` varchar(80) NOT NULL,
  `value` text NOT NULL,
  `type` varchar(10) NOT NULL default '',
  `box` varchar(255) NOT NULL default '',
  `tab` varchar(10) NOT NULL default 'main',
  `sort` tinyint(3) unsigned NOT NULL default '1'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO dou_config VALUES('site_name','DouPHP轻量级企业网站管理系统','text','','main','1');
INSERT INTO dou_config VALUES('site_title','DouPHP轻量级企业网站管理系统','text','','main','2');
INSERT INTO dou_config VALUES('site_keywords','DouPHP,轻量级企业网站管理系统','text','','main','3');
INSERT INTO dou_config VALUES('site_description','DouPHP,轻量级企业网站管理系统','text','','main','4');
INSERT INTO dou_config VALUES('site_logo','logo.gif','file','','main','5');
INSERT INTO dou_config VALUES('site_address','福建省漳州市芗城区','text','','main','6');
INSERT INTO dou_config VALUES('site_closed','0','radio','','main','7');
INSERT INTO dou_config VALUES('icp','','text','','main','8');
INSERT INTO dou_config VALUES('tel','0596-8888888','text','','main','9');
INSERT INTO dou_config VALUES('fax','0596-6666666','text','','main','10');
INSERT INTO dou_config VALUES('qq','','text','','main','11');
INSERT INTO dou_config VALUES('email','your@domain.com','text','','main','12');
INSERT INTO dou_config VALUES('language','zh_cn','select','','main','13');
INSERT INTO dou_config VALUES('rewrite','1','radio','','main','14');
INSERT INTO dou_config VALUES('sitemap','1','radio','','main','15');
INSERT INTO dou_config VALUES('captcha','1','radio','','main','16');
INSERT INTO dou_config VALUES('guestbook_check_chinese','1','radio','','main','17');
INSERT INTO dou_config VALUES('code','','textarea','','main','18');
INSERT INTO dou_config VALUES('thumb_width','135','text','','display','1');
INSERT INTO dou_config VALUES('thumb_height','135','text','','display','2');
INSERT INTO dou_config VALUES('price_decimal','2','text','','display','3');
INSERT INTO dou_config VALUES('display','a:4:{s:7:\"article\";i:10;s:12:\"home_article\";i:5;s:7:\"product\";i:10;s:12:\"home_product\";i:4;}','array','','display','4');
INSERT INTO dou_config VALUES('defined','a:2:{s:7:\"article\";s:0:\"\";s:7:\"product\";s:0:\"\";}','array','','defined','1');
INSERT INTO dou_config VALUES('mail_service','0','radio','','mail','1');
INSERT INTO dou_config VALUES('mail_host','smtp.domain.com','text','','mail','2');
INSERT INTO dou_config VALUES('mail_port','25','text','','mail','3');
INSERT INTO dou_config VALUES('mail_ssl','0','radio','','mail','4');
INSERT INTO dou_config VALUES('mail_username','','text','','mail','5');
INSERT INTO dou_config VALUES('mail_password','','text','','mail','6');
INSERT INTO dou_config VALUES('mobile_name','DouPHP','text','','mobile','1');
INSERT INTO dou_config VALUES('mobile_title','DouPHP触屏版','text','','mobile','2');
INSERT INTO dou_config VALUES('mobile_keywords','DouPHP,DouPHP触屏版','text','','mobile','3');
INSERT INTO dou_config VALUES('mobile_description','DouPHP,DouPHP触屏版','text','','mobile','4');
INSERT INTO dou_config VALUES('mobile_logo','','file','','mobile','5');
INSERT INTO dou_config VALUES('mobile_closed','0','radio','','mobile','6');
INSERT INTO dou_config VALUES('mobile_display','a:4:{s:7:\"article\";i:10;s:12:\"home_article\";i:5;s:7:\"product\";i:10;s:12:\"home_product\";i:4;}','array','','mobile','7');
INSERT INTO dou_config VALUES('site_theme','default','hidden','','','100');
INSERT INTO dou_config VALUES('mobile_theme','default','hidden','','','101');
INSERT INTO dou_config VALUES('build_date','1377768032','hidden','','','102');
INSERT INTO dou_config VALUES('update_number','a:6:{s:6:\"update\";s:1:\"0\";s:5:\"patch\";s:1:\"0\";s:6:\"module\";s:1:\"0\";s:6:\"plugin\";s:1:\"0\";s:5:\"theme\";s:1:\"0\";s:6:\"mobile\";N;}','hidden','','','103');
INSERT INTO dou_config VALUES('update_date','a:3:{s:6:\"system\";a:2:{s:6:\"update\";i:20160909;s:5:\"patch\";i:20160909;}s:6:\"module\";a:2:{s:7:\"article\";i:20160909;s:7:\"product\";i:20160909;}s:5:\"theme\";a:0:{}}','hidden','','','104');
INSERT INTO dou_config VALUES('cloud_account','','hidden','','','105');
INSERT INTO dou_config VALUES('hash_code','166d0de32dafdef9ab26e10130dd115b','hidden','','','106');
INSERT INTO dou_config VALUES('douphp_version','v1.3 Release 20160909','hidden','','','107');