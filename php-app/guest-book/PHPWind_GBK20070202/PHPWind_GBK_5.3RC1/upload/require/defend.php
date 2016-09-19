<?php
//Copyright (c) 2003-06 PHPWind
!function_exists('readover') && exit('Forbidden');

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
if($db_dir && $db_ext){
	$self_array = explode('-',$db_ext ? substr($_SERVER['QUERY_STRING'],0,strpos($_SERVER['QUERY_STRING'],$db_ext)) : $_SERVER['QUERY_STRING']);
	$s_count=count($self_array);
	for($i=0;$i<$s_count;$i++){
		$_key	= $self_array[$i];
		$_value	= $self_array[++$i];
		!ereg("^\_",$_key) && !isset($$_key) && $$_key = addslashes(rawurldecode($_value));
	}
}

if($db_loadavg && substr(PHP_OS,0,3) != 'WIN' && !defined('W_P')) {
	if($fp=@fopen('/proc/loadavg','rb')) {
		list($loadavg)=explode(' ',fread($fp,6));
		fclose($fp);
		$loadavg>$db_loadavg && $db_cc=2;
	}
}

if($db_cc){
	!$_COOKIE && !$_SERVER["HTTP_USER_AGENT"] && exit('Forbidden');
	if($db_cc=='2' && $c_agentip){
		exit('Forbidden');
	}
}
?>