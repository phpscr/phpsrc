<?php
date_default_timezone_set('Asia/Shanghai');
define("TEMPLATE_PATH",dirname(__FILE__)."/template/green");//此处为模板目录，若要更换模板请在这里修改
$mysql = array( // 数据库设置
		'host' => 'localhost', //数据库服务器
		'login' => 'root', //数据库用户名
		'password' => '', //密码
		'database' => 'useforum', //数据库名
		'prefix' => 'useforum_' //前缀，若需更改清先修改数据库文件
	);
