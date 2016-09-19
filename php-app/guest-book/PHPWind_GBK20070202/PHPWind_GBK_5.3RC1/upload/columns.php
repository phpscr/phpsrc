<?php
error_reporting(E_ERROR | E_PARSE);

define('D_P',__FILE__ ? getdirname(__FILE__).'/' : './');
define('R_P',D_P);
define('COL',1);

require_once(R_P.'require/defend.php');
$timestamp= time();

if(strpos($_SERVER['PHP_SELF'],$db_dir)!==false){
	$tmp=substr($_SERVER['PHP_SELF'],0,strpos($_SERVER['PHP_SELF'],$db_dir));
}else{
	$tmp=$_SERVER['PHP_SELF'];
}
$db_bbsurl="http://$_SERVER[HTTP_HOST]".substr($tmp,0,strrpos($tmp,'/'));

$url = ($_SERVER['HTTP_REFERER'] && strpos($_SERVER['HTTP_REFERER'],'admin.php')===false && strpos($_SERVER['HTTP_REFERER'],$db_bbsurl)!==false) ? $_SERVER['HTTP_REFERER'] : $db_bfn;

if($db_columns){
	if($action=='columns'){
		require_once(D_P.'data/sql_config.php');
		$imgpath = $db_http	!= 'N' ? $db_http : $picpath;
		$skin = GetCookie('skinco') ? GetCookie('skinco') : $db_defaultstyle;

		if(file_exists(R_P."data/style/$skin.php") && strpos($skin,'..')===false){
			include_once Pcv(R_P."data/style/$skin.php");
		}else{
			include_once(R_P."data/style/wind.php");
		}
		Cookie('columns',2);
		require_once PrintEot('columns');exit;
	}else{
		Cookie('columns','1');
		echo "<script language=\"JavaScript\">top.location.href=\"".$url."\"</script>";
		exit;
	}
}else{
	ObHeader("index.php");
}

function Cookie($ck_Var,$ck_Value,$ck_Time = 'F'){
	global $db_ckpath,$db_ckdomain,$timestamp;
	$ck_Time = $ck_Time == 'F' ? $timestamp + 31536000 : ($ck_Value == '' && $ck_Time == 0 ? $timestamp - 31536000 : $ck_Time);
	$S		 = $_SERVER['SERVER_PORT'] == '443' ? 1:0;
	!$db_ckpath && $db_ckpath = '/';
	setCookie(CookiePre().'_'.$ck_Var,$ck_Value,$ck_Time,$db_ckpath,$db_ckdomain,$S);
}

function GetCookie($Var){
    return $_COOKIE[CookiePre().'_'.$Var];
}

function CookiePre(){
	return substr(md5($GLOBALS['db_hash']),0,5);
}

function ObHeader($URL){
	global $db_obstart,$db_bbsurl,$db_htmifopen;
	if($db_htmifopen && strtolower(substr($URL,0,4))!='http'){
		$URL="$db_bbsurl/$URL";
	}
	if($db_obstart){
		header("Location: $URL");exit;
	}else{
		ob_start();
		echo "<meta http-equiv='refresh' content='0;url=$URL'>";
		exit;
	}
}

function PrintEot($template,$EXT="htm"){
	//Copyright (c) 2003-06 PHPWind
	global $tplpath;
	if(!$template) $template=N;
	$path=R_P."template/$tplpath/$template.$EXT";
	!file_exists($path) && $path=R_P."template/wind/$template.$EXT";	
	
	return $path;
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

function Pcv($filename,$ifcheck=1){
	strpos($filename,'http://')!==false && exit('Forbidden');
	$ifcheck && strpos($filename,'..')!==false && exit('Forbidden');
	return $filename;
}

function GdConfirm(){
}
function readover($filename,$method="rb"){
	strpos($filename,'..')!==false && exit('Forbidden');
	if($handle=@fopen($filename,$method)){
		flock($handle,LOCK_SH);
		$filedata=@fread($handle,filesize($filename));
		fclose($handle);
	}
	return $filedata;
}
function writeover($filename,$data,$method="rb+",$iflock=1,$check=1,$chmod=1){
	//Copyright (c) 2003-06 PHPWind
	$check && strpos($filename,'..')!==false && exit('Forbidden');
	touch($filename);
	$handle=fopen($filename,$method);
	if($iflock){
		flock($handle,LOCK_EX);
	}
	fwrite($handle,$data);
	if($method=="rb+") ftruncate($handle,strlen($data));
	fclose($handle);
	$chmod && @chmod($filename,0777);
}
function P_unlink($filename){
	strpos($filename,'..')!==false && exit('Forbidden');
	return @unlink($filename);
}
?>