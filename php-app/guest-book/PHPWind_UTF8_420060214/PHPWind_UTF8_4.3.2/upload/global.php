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
$t_array = explode(' ',microtime());
$P_S_T	 = $t_array[0] + $t_array[1];

define('D_P',__FILE__ ? getdirname(__FILE__).'/' : './');
define('R_P',D_P);

unset($_ENV,$HTTP_ENV_VARS,$_REQUEST,$HTTP_POST_VARS,$HTTP_GET_VARS,$HTTP_POST_FILES,$HTTP_COOKIE_VARS);
if(!get_magic_quotes_gpc()){
	Add_S($_POST);
	Add_S($_GET);
	Add_S($_COOKIE);
}
if($_SERVER['HTTP_X_FORWARDED_FOR']){
	$onlineip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	$c_agentip=1;
}elseif($_SERVER['HTTP_CLIENT_IP']){
	$onlineip = $_SERVER['HTTP_CLIENT_IP'];
	$c_agentip=1;
}else{
	$onlineip = $_SERVER['REMOTE_ADDR'];
	$c_agentip=0;
}
$onlineip = substrs(str_replace("\n",'',$onlineip),16);
$timestamp= time();
require_once(R_P.'require/defend.php');
$db_cvtime != 0 && $timestamp += $db_cvtime*60;

if($db_debug){
	error_reporting(E_ALL ^ E_NOTICE);
}
$wind_version = "4.3.2";
$db_olsize	  = 96;
$htmdir		  = 'htm_data';
!$_SERVER['PHP_SELF'] && $_SERVER['PHP_SELF']=$_SERVER['SCRIPT_NAME'];
$REQUEST_URI  = $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
$db_bbsurl="http://$_SERVER[HTTP_HOST]".substr($_SERVER['PHP_SELF'],0,strrpos($_SERVER['PHP_SELF'],'/'));
$fid	  = (int)$fid;
$tid	  = (int)$tid;
$attachname = $js_path = '';
require_once(D_P.'data/bbscache/dbset.php');

$db_obstart == 1 ? ob_start('ob_gzhandler') : ob_start();

$imgpath	= $db_http		!= 'N' ? $db_http	  : $picpath;
$attachpath = $db_attachurl != 'N' ? $db_attachurl : $attachname;
$imgdir		= R_P.$picpath;
$attachdir	= R_P.$attachname;

if(D_P != R_P && $db_http != 'N'){
	$R_url=substr($db_http,-1)=='/' ?  substr($db_http,0,-1) : $db_http;
	$R_url=substr($R_url,0,strrpos($R_url,'/'));
}else{
	$R_url=$db_bbsurl;
}

if(GetCookie('lastvisit')){
	list($c_oltime,$lastvisit,$lastpath) = explode("\t",GetCookie('lastvisit'));
	($onbbstime=$timestamp-$lastvisit)<$db_onlinetime && $c_oltime+=$onbbstime;
}else{
	$lastvisit=$lastpath='';
	$c_oltime=0;
}
$ol_offset = GetCookie('ol_offset');
$skinco	   = GetCookie('skinco');
if ($db_refreshtime!=0){
	if($REQUEST_URI==$lastpath && $onbbstime<$db_refreshtime){
		!GetCookie('winduser') && $groupid='guest';
		$manager=TRUE;
		$skin = $skinco ? $skinco : $db_defaultstyle;
		Showmsg("refresh_limit");
	}
}
$H_url =& $db_wwwurl;
$B_url =& $db_bbsurl;
require_once(D_P.'data/sql_config.php');

if ($db_bbsifopen==0){
	$CK = explode("\t",StrCode(GetCookie('AdminUser'),'DECODE'));
	if ($timestamp-$CK[0]>1800 || $CK[1]!=$manager || !SafeCheck($CK,PwdCode($manager_pwd))){
		$skin	 = $skinco ? $skinco : $db_defaultstyle;
		$groupid = '';
		Showmsg($db_whybbsclose);
	}
}
$t		= array('hours'=>gmdate('G',$timestamp+$db_timedf*3600));
$tdtime	= (floor($timestamp/3600)-$t['hours'])*3600;
$runfc	= 'N';
if($timestamp-$lastvisit>$db_onlinetime || ($fid && $fid != GetCookie('lastfid')) || (GetCookie('lastfid') && $wind_in=='hm')){
	Cookie('lastfid',$fid);
	$runfc='Y';
	require_once(R_P.'require/userglobal.php');
}

require_once(R_P.'require/db_'.$database.'.php');
$db = new DB($dbhost, $dbuser, $dbpw, $dbname, $pconnect);
unset($dbhost,$dbuser,$dbpw,$dbname,$pconnect,$manager_pwd);
list($winduid,$windpwd)=explode("\t",StrCode(GetCookie('winduser'),'DECODE'));
if($winduid && strlen($windpwd)>=16){
	$winddb	  = User_info();
	$winduid  = $winddb['uid'];
	$groupid  = $winddb['groupid'];
	$userrvrc = (int)($winddb['rvrc']/10);
	$windid	  = $winddb['username'];
	$_datefm  = $winddb['datefm'];
	$_timedf  = $winddb['timedf'];
	$skin	  = $winddb['style'] ? $winddb['style'] : $db_defaultstyle;
	$winddb['onlineip']=substr($winddb['onlineip'],0,strpos($winddb['onlineip'],'|'));
	$groupid=='-1' && $groupid=$winddb['memberid'];
	if($winddb['showsign'] && (!$winddb['starttime'] && $db_signmoney && strpos($db_signgroup,",$groupid,") !== false && $winddb['currency'] > $db_signmoney || $winddb['starttime'] && $winddb['starttime'] != $tdtime)){
		require_once(R_P.'require/Signfunc.php');
		Signfunc($winddb['showsign'],$winddb['starttime'],$winddb['currency']);
	}
} else{
	$skin	 = $db_defaultstyle;
	$groupid = 'guest';
	unset($winddb);
	$windid=$winduid=$_datefm=$_timedf='';
}
$_GET['skinco'] && $skinco=$_GET['skinco'];
$_POST['skinco'] && $skinco=$_POST['skinco'];
if($skinco && file_exists(D_P."data/style/$skinco.php") && strpos($skinco,'..')===false){
	$skin=$skinco;
	Cookie('skinco',$skinco);
}
Ipban();
Cookie('lastvisit',$c_oltime."\t".$timestamp."\t".$REQUEST_URI);
//if(GetCookie('pwdcheck') && $groupid=='guest') Cookie('pwdcheck','',0);
if($groupid!='guest'){
	if(file_exists(D_P."data/groupdb/group_$groupid.php")){
		require_once(D_P."data/groupdb/group_$groupid.php");
	}else{
		require_once(D_P."data/groupdb/group_1.php");
	}
} else{
	require_once(D_P."data/groupdb/group_2.php");
}
if($db_ads && !$windid && (is_numeric($u) || ($a && strlen($a)<16)) && strpos($_SERVER['HTTP_REFERER'],$_SERVER['HTTP_HOST'])===false){
	Cookie('userads',"$u\t$a");
}
if(!defined('SCR')){
	define('SCR','other');
}
$SCR = SCR;
$header_ad=$footer_ad='';
if(SCR != 'read'){
	$advertdb = AdvertInit(SCR,$fid);
	if(is_array($advertdb['header'])){
		$header_ad = $advertdb['header'][array_rand($advertdb['header'])]['code'];
	}
	if(is_array($advertdb['footer'])){
		$footer_ad = $advertdb['footer'][array_rand($advertdb['footer'])]['code'];
	}
}
function headguide($guidename=array(),$guide=''){
	global $fid,$jinhua;
	if(is_array($guidename)){
		foreach($guidename as $key=>$value){
			if($key){
				$headguide.=$value ? " -&gt; <a href='$value'>$key</a>" : " -&gt; $key";
			}
		}
	} else{
		$headguide.=" -&gt; ".$guidename;
	}
	return $headguide;
}
function refreshto($URL,$content,$statime=1){
	global $db_ifjump;
	$URL=str_replace('&#61;','=',$URL);
	if($db_ifjump && $statime>0){
		ob_end_clean();
		global $tplpath,$fid,$imgpath,$db_obstart,$db_bbsname,$skin,$B_url;
		$index_name =& $db_bbsname;
		$index_url =& $B_url;
		$db_obstart==1 ? ob_start('ob_gzhandler') : ob_start();
		if(file_exists(D_P."data/style/$skin.php") && strpos($skin,'..')===false){
			include_once(D_P."data/style/$skin.php");
		}else{
			include_once(D_P."data/style/wind.php");
		}
		@extract($GLOBALS, EXTR_SKIP);
		require_once GetLang('refreshto');
		$lang[$content] && $content=$lang[$content];
		@require PrintEot('refreshto');
		exit;
	} else{
		ObHeader($URL);
	}
}
function ObHeader($URL){
	if($GLOBALS[db_obstart]){
		header("Location: $URL");exit;
	}else{
		ob_start();
		echo "<meta http-equiv='refresh' content='0;url=$URL'>";
		exit;
	}
}
function Showmsg($msg_info,$dejump=0){

	@extract($GLOBALS, EXTR_SKIP);
	global $stylepath,$tablewidth,$mtablewidth,$tplpath,$runfc;
	$runfc='';
	if(defined('SIMPLE')){
		echo "<base href=\"$db_bbsurl/\">";
	}
	require_once(R_P.'require/header.php');
	require_once GetLang('msg');
	$lang[$msg_info] && $msg_info=$lang[$msg_info];

	require_once PrintEot('showmsg');
	exit;
}
function GetLang($lang,$EXT="php"){
	global $tplpath;
	$path=R_P."template/$tplpath/lang_$lang.$EXT";
	!file_exists($path) && $path=R_P."template/wind/lang_$lang.$EXT";
	return $path;
}
function PrintEot($template,$EXT="htm"){
	//Copyright (c) 2003-06 PHPWind
	global $tplpath;
	if(!$template) $template=N;
	$path=R_P."template/$tplpath/$template.$EXT";
	!file_exists($path) && $path=R_P."template/wind/$template.$EXT";
	return $path;
}
function Cookie($ck_Var,$ck_Value,$ck_Time = 'F'){
	global $db_ckpath,$db_ckdomain,$timestamp;
	$ck_Time = $ck_Time == 'F' ? $timestamp + 31536000 : ($ck_Value == '' && $ck_Time == 0 ? $timestamp - 31536000 : $ck_Time);
	$S		 = $_SERVER['SERVER_PORT'] == '443' ? 1:0;
	!$db_ckpath && $db_ckpath = '/';
	setCookie($ck_Var,$ck_Value,$ck_Time,$db_ckpath,$db_ckdomain,$S);
}
function GetCookie($Var){
	return $_COOKIE[$Var];
}
function Ipban(){
	global $db_ipban,$onlineip,$imgpath,$stylepath;
	if($db_ipban){
		$baniparray=explode(",",$db_ipban);
		foreach($baniparray as $banip){
			if(!$banip)continue;
			$banip=trim($banip);
			if(strpos(','.$onlineip.'.',','.$banip.'.')!==false){
				Showmsg('ip_ban');
			}
		}
	}
}
function P_unlink($filename){
	strpos($filename,'..')!==false && exit('Forbidden');
	@unlink($filename);
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
function openfile($filename){
	$filedata=readover($filename);
	$filedata=str_replace("\n","\n<:wind:>",$filedata);
	$filedb=explode("<:wind:>",$filedata);
	$count=count($filedb);
	if($filedb[$count-1]==''||$filedb[$count-1]=="\r"){unset($filedb[$count-1]);}
	if(empty($filedb)){$filedb[0]="";}
	return $filedb;
}
function Update_ol(){
	global $runfc;
	if($runfc == 'Y'){
		global $ol_offset,$winduid,$db_ipstates,$isModify;
		if($winduid != ''){
			list($alt_offset,$isModify) = addonlinefile($ol_offset,$winduid);
		}else{
			list($alt_offset,$isModify) = addguestfile($ol_offset);
		}
		if($alt_offset!=$ol_offset)Cookie('ol_offset',$alt_offset,0);
		$runfc='';
		if($db_ipstates && ((!GetCookie('ipstate') && $isModify===1) || (GetCookie('ipstate') && GetCookie('ipstate')<$GLOBALS['tdtime']))){
			require_once(R_P.'require/ipstates.php');
		}
	}
}
function footer(){
	global $db,$db_obstart,$db_footertime,$P_S_T,$mtablewidth,$db_ceoconnect,$wind_version,$imgpath,$stylepath,$footer_ad,$db_union,$timestamp;
	Update_ol();
	if($db){
		$qn=$db->query_num;
	}
	$ft_gzip=($db_obstart==1 ? "Gzip enabled" : "Gzip disabled").$db_union[3];
	if ($db_footertime == 1){
		$t_array	= explode(' ',microtime());
		$totaltime	= number_format(($t_array[0]+$t_array[1]-$P_S_T),6);
		$wind_spend	= "Total $totaltime(s) query $qn,";
	}
	$ft_time=get_date($timestamp,'m-d H:i');
	include PrintEot('footer');
	$output = str_replace(array('<!--<!---->','<!---->'),array('',''),ob_get_contents());

	ob_end_clean();
	$db_obstart == 1 ? ob_start('ob_gzhandler') : ob_start();
	echo $output;
	flush;
	exit;
}
function User_info(){
	global $db,$timestamp,$db_onlinetime,$winduid,$windpwd,$db_ifonlinetime,$c_oltime,$onlineip,$db_ipcheck,$tdtime;
	$ct='';
	$detail =$db->get_one("SELECT m.uid,m.username,m.password,m.email,oicq,m.groupid,m.memberid,m.regdate,m.timedf,m.style,m.datefm,m.t_num,m.p_num,m.yz,m.newpm,m.showsign,m.payemail,md.postnum,md.rvrc,md.money,md.credit,md.currency,md.lastvisit,md.thisvisit,md.onlinetime,md.lastpost,md.todaypost,md.onlineip,md.uploadtime,md.uploadnum,md.editor,md.starttime FROM pw_members m LEFT JOIN pw_memberdata md USING(uid) WHERE m.uid='$winduid'");
	if(strpos($detail['onlineip'],$onlineip)===false){
		$iparray=explode(".",$onlineip);
		if(strpos($detail['onlineip'],$iparray[0].'.'.$iparray[1])===false) $loginout='Y';
	}
	if(!$detail || PwdCode($detail['password']) != $windpwd || ($loginout=='Y' && $db_ipcheck==1)){
		unset($detail);
		$GLOBALS['groupid']='guest';
		require_once(R_P.'require/checkpass.php');
		Loginout();
		Showmsg('ip_change');
	}else{
		unset($detail['password']);
		if($timestamp-$detail['thisvisit']>$db_onlinetime){
			if(!GetCookie('hideid')){
				$ct="lastvisit=thisvisit,thisvisit='$timestamp'";
				$detail['lastvisit'] = $detail['thisvisit'];
				$detail['thisvisit'] = $timestamp;
			}
			if($db_ifonlinetime == 1 && $ct && $c_oltime > 0){
				if($c_oltime > $db_onlinetime*1.2){
					$c_oltime = $db_onlinetime;
				}
				$ct		 .= ",onlinetime=onlinetime+'$c_oltime'";
				$c_oltime = 0;
			}
			$ct && $db->update("UPDATE pw_memberdata SET $ct WHERE uid='$winduid' AND $timestamp-thisvisit>$db_onlinetime");
		}
	}
	return $detail;
}
function PwdCode($pwd){
	return md5($_SERVER["HTTP_USER_AGENT"].$pwd.$GLOBALS['db_hash']);
}
function SafeCheck($CK,$PwdCode,$var='AdminUser',$expire=1800){
	global $timestamp;
	$t	= $timestamp - $CK[0];
	if($t > $expire || $CK[2] != md5($PwdCode.$CK[0])){
		Cookie($var,'',0);
		return false;
	}else{
		$CK[0] = $timestamp;
		$CK[2] = md5($PwdCode.$timestamp);
		$Value = implode("\t",$CK);
		$$var  = StrCode($Value);
		Cookie($var,StrCode($Value));
		return true;
	}
}
function StrCode($string,$action='ENCODE'){
	$key	= substr(md5($_SERVER["HTTP_USER_AGENT"].$GLOBALS['db_hash']),8,18);
	$string	= $action == 'ENCODE' ? $string : base64_decode($string);
	$len	= strlen($key);
	$code	= '';
	for($i=0; $i<strlen($string); $i++){
		$k		= $i % $len;
		$code  .= $string[$i] ^ $key[$k];
	}
	$code = $action == 'DECODE' ? $code : base64_encode($code);
	return $code;
}
function substrs($content,$length){
	if($length && strlen($content)>$length){
		$num=0;
		for($i=0;$i<$length-3;$i++){
			if(ord($content[$i])>127){
				$num++;
			}
		}
		$num%2==1 ? $content=substr($content,0,$length-4):$content=substr($content,0,$length-3);
		$content.='..';
	}
	return $content;
}
function get_date($timestamp,$timeformat=''){
	global $db_datefm,$db_timedf,$_datefm,$_timedf;
	$date_show=$timeformat ? $timeformat : ($_datefm ? $_datefm : $db_datefm);
	if($_timedf){
		$offset = $_timedf=='111' ? 0 : $_timedf;
	}else{
		$offset = $db_timedf=='111' ? 0 : $db_timedf;
	}
	return gmdate($date_show,$timestamp+$offset*3600);
}
function Add_S(&$array){
	foreach($array as $key=>$value){
		if(!is_array($value)){
			$array[$key]=addslashes($value);
		}else{
			Add_S($array[$key]);
		}
	}
}
function Char_cv($msg){
	$msg = str_replace('&amp;','&',$msg);
	$msg = str_replace('&nbsp;',' ',$msg);
	$msg = str_replace('"','&quot;',$msg);
	$msg = str_replace("'",'&#39;',$msg);
	$msg = str_replace("<","&lt;",$msg);
	$msg = str_replace(">","&gt;",$msg);
	$msg = str_replace("\t"," &nbsp; &nbsp;",$msg);
	$msg = str_replace("\r","",$msg);
	$msg = str_replace("   "," &nbsp; ",$msg);
	return $msg;
}
function GdConfirm($code){
	Cookie('cknum','',0);
	if(!$code || !SafeCheck(explode("\t",StrCode(GetCookie('cknum'),'DECODE')),$code,'cknum',1800)){
		Showmsg('check_error');
	}
}
function AdvertInit($SCR,$fid){
	global $timestamp;
	include(D_P.'data/bbscache/advert_data.php');
	$newadvert = array();
	foreach($advertdb as $key=>$val){
		foreach($val as $k=>$v){
			if(($key=='header' || $key=='footer') && !$v['endtime'] || strtotime($v['endtime']) < $timestamp){
				continue;
			}
			if($SCR == 'index' && strpos(",$v[fid],",",-1,")!==false){
				$newadvert[$key][]=$v;
			}elseif($SCR == 'thread' && strpos(",$v[fid],",",-2,")!==false){
				$newadvert[$key][]=$v;
			}elseif($SCR == 'read' && strpos(",$v[fid],",",-3,")!==false){
				$newadvert[$key][]=$v;
			}elseif(strpos(",$v[fid],",",-4,")!==false){
				$newadvert[$key][]=$v;
			}elseif($fid && strpos(",$v[fid],",",$fid,")!==false){
				$newadvert[$key][]=$v;
			}
		}
	}
	return $newadvert;
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