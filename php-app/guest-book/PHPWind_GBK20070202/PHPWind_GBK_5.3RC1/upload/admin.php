<?php
/**
*
*  Copyright (c) 2003-06  PHPWind.net. All rights reserved.
*  Support : http://www.phpwind.net
*  This software is the proprietary information of PHPWind.com.
*
*/
error_reporting(E_ERROR | E_PARSE);
set_magic_quotes_runtime(0);

define('D_P',__FILE__ ? getdirname(__FILE__).'/' : './');
define('R_P',D_P);

$admin_file = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
require_once(R_P."admin/admincp.php");

if (!$adminjob){
	require_once PrintEot('index');	exit;
} elseif ($adminjob=='notice'){
	require_once(R_P."admin/notice.php");
} elseif ($adminjob == 'admin'){
	$query = $db->query("SHOW TABLE STATUS");
	while ($rs = $db->fetch_array($query)) {
		if (ereg("^$PW",$rs['Name'])){
			$pw_size = $pw_size + $rs['Data_length'] + $rs['Index_length'];
		} else{
			$o_size = $o_size + $rs['Data_length'] + $rs['Index_length'];
		}
	}

	$o_size		= number_format($o_size/(1024*1024),2);
	$pw_size	= number_format($pw_size/(1024*1024),2);
	$systemtime	= gmdate("Y-m-d H:i",time()+$db_timedf*3600);
	$altertime	= gmdate("Y-m-d H:i",$timestamp+$db_timedf*3600);
	$sysversion = PHP_VERSION;
	$sysos      = $_SERVER['SERVER_SOFTWARE'];
	$max_upload = ini_get('file_uploads') ? ini_get('upload_max_filesize') : 'Disabled';
	$max_ex_time= ini_get('max_execution_time').' seconds';
	$sys_mail   = ini_get('sendmail_path') ? 'Unix Sendmail ( Path: '.ini_get('sendmail_path').')' :( ini_get('SMTP') ? 'SMTP ( Server: '.ini_get('SMTP').')': 'Disabled' );
	$ifcookie   = isset($_COOKIE) ? "SUCCESS" : "FAIL";
	@extract($db->get_one("SELECT VERSION() AS dbversion"));

	require_once PrintEot('admin');exit;
} elseif (in_array($adminjob,array('rightset','manager','code')) && If_manager){
	require_once(R_P."admin/$adminjob.php");
} elseif ($adminjob == 'hackcenter' && (If_manager || $admin_gid == 3)){
	require_once(R_P."admin/hackcenter.php");
} elseif ($adminjob == 'hack' && $admin_gid == 3){
	if (!$db_hackdb[$hackset] || !is_dir(R_P."hack/$hackset") || !file_exists(R_P."hack/$hackset/admin.php")){
		adminmsg("hack_error");
	}
	define('H_P',R_P."hack/$hackset/");
	$basename="$admin_file?adminjob=hack&hackset=$hackset";
	require_once Pcv(H_P."admin.php");
} elseif ($adminjob == 'content' && ($rightset['tpccheck'] && $type == 'tpc' || $rightset['postcheck'] && $type == 'post' || $rightset['message'] && $type == 'message')){
	require_once(R_P."admin/content.php");
} elseif ($rightset[$adminjob] || ($a_type && $rightset[$a_type])){
	require_once Pcv(R_P."admin/$adminjob.php");
} elseif ($adminjob == 'left'){
	require_once(R_P."admin/left.php");
} else {
	adminmsg('undefine_action');
}
function SafeFunc(){
	//Safe The Admin
}
function getdirname($path){
	if(strpos($path,'\\')!==false){
		return substr($path,0,strrpos($path,'\\'));
	}elseif(strpos($path,'/')!==false){
		return substr($path,0,strrpos($path,'/'));
	}else{
		return '/';
	}
}
?>