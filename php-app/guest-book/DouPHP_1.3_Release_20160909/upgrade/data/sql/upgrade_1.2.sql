-------------------------------------------------------------------
-- 以下为升级至1.2版本
-------------------------------------------------------------------
ALTER TABLE `dou_show` ADD `type` varchar(10) NOT NULL AFTER `show_img`;

-- 数据修改
UPDATE `dou_show` SET `type` = 'pc' WHERE `type` = '';

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
INSERT INTO dou_config VALUES('site_theme','default','select','','main','5');
INSERT INTO dou_config VALUES('site_logo','logo.gif','file','','main','6');
INSERT INTO dou_config VALUES('site_address','福建省漳州市芗城区','text','','main','7');
INSERT INTO dou_config VALUES('site_closed','0','radio','','main','8');
INSERT INTO dou_config VALUES('icp','','text','','main','9');
INSERT INTO dou_config VALUES('tel','0596-8888888','text','','main','10');
INSERT INTO dou_config VALUES('fax','0596-6666666','text','','main','11');
INSERT INTO dou_config VALUES('qq','','text','','main','12');
INSERT INTO dou_config VALUES('email','your@domain.com','text','','main','13');
INSERT INTO dou_config VALUES('language','zh_cn','select','','main','14');
INSERT INTO dou_config VALUES('rewrite','1','radio','','main','15');
INSERT INTO dou_config VALUES('sitemap','1','radio','','main','16');
INSERT INTO dou_config VALUES('captcha','1','radio','','main','17');
INSERT INTO dou_config VALUES('guestbook_check_chinese','1','radio','','main','18');
INSERT INTO dou_config VALUES('code','','textarea','','main','19');
INSERT INTO dou_config VALUES('display_product','10','text','','display','1');
INSERT INTO dou_config VALUES('display_article','10','text','','display','2');
INSERT INTO dou_config VALUES('display_guestbook','10','text','','display','3');
INSERT INTO dou_config VALUES('home_display_product','4','text','','display','4');
INSERT INTO dou_config VALUES('home_display_article','5','text','','display','5');
INSERT INTO dou_config VALUES('thumb_width','135','text','','display','6');
INSERT INTO dou_config VALUES('thumb_height','135','text','','display','7');
INSERT INTO dou_config VALUES('price_decimal','2','text','','display','8');
INSERT INTO dou_config VALUES('defined_product','','text','','defined','1');
INSERT INTO dou_config VALUES('defined_article','','text','','defined','2');
INSERT INTO dou_config VALUES('mobile_name','DouPHP','text','','mobile','1');
INSERT INTO dou_config VALUES('mobile_title','DouPHP触屏版','text','','mobile','2');
INSERT INTO dou_config VALUES('mobile_keywords','DouPHP,DouPHP触屏版','text','','mobile','3');
INSERT INTO dou_config VALUES('mobile_description','DouPHP,DouPHP触屏版','text','','mobile','4');
INSERT INTO dou_config VALUES('mobile_theme','default','select','','mobile','5');
INSERT INTO dou_config VALUES('mobile_logo','','file','','mobile','6');
INSERT INTO dou_config VALUES('mobile_display_product','10','text','','mobile','7');
INSERT INTO dou_config VALUES('mobile_display_article','10','text','','mobile','8');
INSERT INTO dou_config VALUES('mobile_display_guestbook','10','text','','mobile','9');
INSERT INTO dou_config VALUES('mobile_home_display_product','6','text','','mobile','10');
INSERT INTO dou_config VALUES('mobile_home_display_article','6','text','','mobile','11');
INSERT INTO dou_config VALUES('build_date','1377768032','hidden','','','100');
INSERT INTO dou_config VALUES('hash_code','166d0de32dafdef9ab26e10130dd115b','hidden','','','101');
INSERT INTO dou_config VALUES('douphp_version','v1.2 Alpha1 20140930','hidden','','','102');