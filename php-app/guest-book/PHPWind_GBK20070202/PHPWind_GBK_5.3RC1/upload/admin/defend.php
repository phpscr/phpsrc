<?php
!function_exists('adminmsg') && exit('Forbidden');

foreach($_POST as $_key=>$_value){
	!ereg("^\_",$_key) && !isset($$_key) && $$_key=$_POST[$_key];
}
foreach($_GET as $_key=>$_value){
	!ereg("^\_",$_key) && !isset($$_key) && $$_key=$_GET[$_key];
}
require_once(D_P.'data/bbscache/config.php');
if($db_forcecharset && !defined('W_P')){
	@header("Content-Type: text/html; charset=$db_charset");
}
strpos($adminjob,'..') !== false && exit('Forbidden');

if($db_cc && !defined('COL')){
	!$_COOKIE && !$_SERVER["HTTP_USER_AGENT"] && exit('Forbidden');
	if($db_cc=='2' && $c_agentip){
		exit('Forbidden');
	}
}
?>