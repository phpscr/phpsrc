<?php
/**
* 以下变量需根据您的服务器说明档修改
*/
$dbhost = 'localhost';			// 数据库服务器
$dbuser = '';			// 数据库用户名
$dbpw = '';				// 数据库密码
$dbname = '';			// 数据库名
$database = 'mysql';			// 数据库类型
$PW = 'pw_';					// 表区分符
$pconnect = 0;			// 是否持久连接

/*
MYSQL编码设置
如果您的论坛出现乱码现象，需要设置此项来修复
请不要随意更改此项，否则将可能导致论坛出现乱码现象
*/
$charset='gbk';

/**
* 论坛创始人,拥有论坛所有权限
*/
$manager='';			// 管理员用户名
$manager_pwd='';	// 管理员密码
$adminemail = '';	// 论坛系统 Email

/**
* 镜像站点设置
*/
$db_hostweb=1;		// 是否为主站点

/*
* 附件url地址，以http:// 开头的绝对地址  为空使用默认
*/
$attach_url='';



/**
* 插件配置
*/
$db_hackdb=array(

		'bank'=>array('银行','bank','bank.php','bankset.php','1','hack/bank.php,hack/bankset.php,template/wind/bank.htm,template/admin/bankset.htm'),
		'colony'=>array('PW群','colony','colony.php','colonyset.php','1','colony.php,colonyset.php,template/admin/colonyset.htm,template/wind/colony.htm,data/bbscache/cn_cache.php'),
		
		);
?>