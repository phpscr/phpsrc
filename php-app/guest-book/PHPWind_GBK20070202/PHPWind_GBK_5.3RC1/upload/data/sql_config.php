<?php
/**
* 以下变量需根据您的服务器说明档修改
*/
$dbhost = 'localhost';			// 数据库服务器
$dbuser = 'root';			// 数据库用户名
$dbpw = '';				// 数据库密码
$dbname = 'phpwind';			// 数据库名
$database = 'mysql';			// 数据库类型
$PW = 'pw_';					// 表区分符
$pconnect = '0';		// 是否持久连接

/*
MYSQL编码设置
如果您的论坛出现乱码现象，需要设置此项来修复
请不要随意更改此项，否则将可能导致论坛出现乱码现象
*/
$charset='';

/**
* 论坛创始人,拥有论坛所有权限
*/
$manager='admin';			//管理员用户名
$manager_pwd='';	//管理员密码

/**
* 镜像站点设置
*/
$db_hostweb='1';		//是否为主站点

/*
* 附件url地址，以http:// 开头的绝对地址  为空使用默认
*/
$attach_url=array();

/*
* 图片附件目录配置
*/
$picpath='image';
$attachname='attachment';


/**
* 插件配置
*/
$db_hackdb=array(

		'bank'=>array('银行','bank','1'),
		'colony'=>array('朋友圈','colony','1'),
		'advert'=>array('广告管理','advert','0'),
		'new'=>array('首页调用管理','new','0'),
		'medal'=>array('勋章中心','medal','1'),
		'toolcenter'=>array('道具中心','toolcenter','1'),
		'blog'=>array('博客','blog','1'),
		'invite'=>array('邀请注册','invite','1'),
		'passport'=>array('通行证','passport','0'),
		'team'=>array('团队管理工资设置','team','1'),
		'nav'=>array('自定义导航','nav','0'),
		
		);
?>