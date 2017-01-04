<?php
//header('Content-Type: text/html; charset=utf-8');


$config=array(
	"name"			=>"luocms文章管理系统",
	"url"			=>"http://www.9luo.cn",
	"keywords"		=>"luocms,文章管理系统,CMS",
	"description"	=>"非常简单的文章管理系统，适合建立一些功能要求不高的公司、企业、政府网站",
	"icp"			=>"ICP备案号",
	"masteremail"	=>"luoquanlu@qq.com"
);

define ('DB_TYPE','mysql');
define ('DB_HOST','localhost');
define ('DB_USER','root');
define ('DB_PWD','root');
define ('DB_NAME','db_luo');
define ('DB_CHARSET','GBK');	

require_once 'db_function.php';	//数据库操作类
require_once 'function.php';   //引用函数



/*------------------------------------------------
 * 数据库连接
 *-----------------------------------------------*/
$db = new db_mysql();
$db->connect(DB_HOST,DB_USER,DB_PWD,DB_NAME,DB_CHARSET);

/*防止 PHP 5.1.x 使用时间函数报错*/
if(function_exists('date_default_timezone_set')) date_default_timezone_set('PRC');
?>