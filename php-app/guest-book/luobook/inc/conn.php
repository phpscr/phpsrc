<?php
//header('Content-Type: text/html; charset=utf-8');


$config=array(
	"name"			=>"luocms���¹���ϵͳ",
	"url"			=>"http://www.9luo.cn",
	"keywords"		=>"luocms,���¹���ϵͳ,CMS",
	"description"	=>"�ǳ��򵥵����¹���ϵͳ���ʺϽ���һЩ����Ҫ�󲻸ߵĹ�˾����ҵ��������վ",
	"icp"			=>"ICP������",
	"masteremail"	=>"luoquanlu@qq.com"
);

define ('DB_TYPE','mysql');
define ('DB_HOST','localhost');
define ('DB_USER','root');
define ('DB_PWD','root');
define ('DB_NAME','db_luo');
define ('DB_CHARSET','GBK');	

require_once 'db_function.php';	//���ݿ������
require_once 'function.php';   //���ú���



/*------------------------------------------------
 * ���ݿ�����
 *-----------------------------------------------*/
$db = new db_mysql();
$db->connect(DB_HOST,DB_USER,DB_PWD,DB_NAME,DB_CHARSET);

/*��ֹ PHP 5.1.x ʹ��ʱ�亯������*/
if(function_exists('date_default_timezone_set')) date_default_timezone_set('PRC');
?>