<?php
/**
*
*  Copyright (c) 2003-06  PHPWind.net. All rights reserved.
*  Support : http://www.phpwind.net
*  This software is the proprietary information of PHPWind.com.
*
*/
//Dev Code

error_reporting(E_ERROR | E_PARSE);

set_magic_quotes_runtime(0);
$t_array = explode(' ',microtime());
$P_S_T	 = $t_array[0] + $t_array[1];

define('D_P',__FILE__ ? getdirname(__FILE__).'/' : './');
define('R_P',D_P);
$htmdir = 'htm_data';

function_exists('date_default_timezone_set') && date_default_timezone_set('Etc/GMT+0');

unset($_ENV,$HTTP_ENV_VARS,$_REQUEST,$HTTP_POST_VARS,$HTTP_GET_VARS,$HTTP_POST_FILES,$HTTP_COOKIE_VARS);
if(!get_magic_quotes_gpc()){
	Add_S($_POST);
	Add_S($_GET);
	Add_S($_COOKIE);
}
Add_S($_FILES);
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
$onlineip = preg_match("/^[\d]([\d\.]){5,13}[\d]$/", $onlineip) ? $onlineip : 'unknown';
$timestamp= time();
require_once(R_P.'require/defend.php');
$db_cvtime != 0 && $timestamp += $db_cvtime*60;

if($db_debug){
	error_reporting(E_ALL ^ E_NOTICE);
}
$wind_version = "5.3 RC1";
$db_olsize	  = 96;

!$_SERVER['PHP_SELF'] && $_SERVER['PHP_SELF']=$_SERVER['SCRIPT_NAME'];
$REQUEST_URI  = $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
if(strpos($_SERVER['PHP_SELF'],$db_dir)!==false){
	$tmp=substr($_SERVER['PHP_SELF'],0,strpos($_SERVER['PHP_SELF'],$db_dir));
}else{
	$tmp=$_SERVER['PHP_SELF'];
}
$db_bbsurl="http://$_SERVER[HTTP_HOST]".substr($tmp,0,strrpos($tmp,'/'));

$fid	  = (int)$fid;
$tid	  = (int)$tid;
$js_path  = '';
$db_obstart == 1 && function_exists('ob_gzhandler') ? ob_start('ob_gzhandler') : ob_start();

require_once(D_P.'data/sql_config.php');
$imgpath	= $db_http		!= 'N' ? $db_http	   : $picpath;
$attachpath = $db_attachurl != 'N' ? $db_attachurl : $attachname;
$imgdir		= R_P.$picpath;
$attachdir	= R_P.$attachname;
$pw_posts   = 'pw_posts';

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
if($db_refreshtime){
	if($REQUEST_URI==$lastpath && $onbbstime<$db_refreshtime){
		!GetCookie('winduser') && $groupid='guest';
		$manager=TRUE;
		$skin = $skinco ? $skinco : $db_defaultstyle;
		Showmsg("refresh_limit");
	}
}
$H_url =& $db_wwwurl;
$B_url =& $db_bbsurl;

if($db_bbsifopen==0){
	require_once(R_P.'require/bbsclose.php');
}
$t		= array('hours'=>gmdate('G',$timestamp+$db_timedf*3600));
$tddays = get_date($timestamp,'j');
$tdtime	= (floor($timestamp/3600)-$t['hours'])*3600;
$montime= $tdtime-($tddays-1)*86400;

$runfc	= 'N';
if($timestamp-$lastvisit>$db_onlinetime || ($fid && $fid != GetCookie('lastfid')) || (GetCookie('lastfid') && $wind_in=='hm')){
	Cookie('lastfid',$fid);
	$runfc='Y';
	require_once(R_P.'require/userglobal.php');
}

require_once Pcv(R_P.'require/db_'.$database.'.php');
$db = new DB($dbhost, $dbuser, $dbpw, $dbname, $pconnect);
unset($dbhost,$dbuser,$dbpw,$dbname,$pconnect,$manager_pwd);
list($winduid,$windpwd) = explode("\t",StrCode(GetCookie('winduser'),'DECODE'));
if(is_numeric($winduid) && strlen($windpwd)>=16){
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
	$winddb  = $windid=$winduid=$_datefm=$_timedf='';
}

if($passport_ifopen && $passport_type == 'client'){
	$loginurl	= "$passport_serverurl/$passport_loginurl?forward=".rawurlencode($db_bbsurl);
	$loginouturl= "$passport_serverurl/$passport_loginouturl&forward=".rawurlencode($db_bbsurl);
	$regurl		= "$passport_serverurl/$passport_regurl?forward=".rawurlencode($db_bbsurl);
}else{
	$loginurl	= "login.php";
	$loginouturl= "login.php?action=quit";
	$regurl		= "register.php";
}
if($db_ads && !$windid && (is_numeric($u) || ($a && strlen($a)<16)) && strpos($_SERVER['HTTP_REFERER'],$_SERVER['HTTP_HOST'])===false){
	Cookie('userads',"$u\t$a\t".md5($_SERVER['HTTP_REFERER']));
}elseif(GetCookie('userads') && $db_ads=='1'){
	list($u,$a)=explode("\t",GetCookie('userads'));
	if(is_numeric($u) || ($a && strlen($a)<16)){
		require_once(R_P.'require/userads.php');
	}
}

$_GET['skinco'] && $skinco=$_GET['skinco'];
$_POST['skinco'] && $skinco=$_POST['skinco'];
if($skinco && file_exists(R_P."data/style/$skinco.php") && strpos($skinco,'..')===false){
	$skin=$skinco;
	Cookie('skinco',$skinco);
}
if($db_columns && !defined('W_P') && !defined('SIMPLE')){
	if(!GetCookie('columns')){
		$j_columns=$db_columns==2 ? 1 : '';
		Cookie('columns',$db_columns);
	}
	if(($j_columns || GetCookie('columns')==2) && (strpos($_SERVER['HTTP_REFERER'],$db_bbsurl)===false || strpos($_SERVER['HTTP_REFERER'],'admin.php')!==false)){
		strpos($REQUEST_URI,'index.php')===false ? Cookie('columns','1') : ObHeader("columns.php?action=columns");
	}
}
Ipban();
Cookie('lastvisit',$c_oltime."\t".$timestamp."\t".$REQUEST_URI);

unset($db_whybbsclose,$db_whycmsclose,$db_ipban);
if($groupid!='guest'){
	if(file_exists(D_P."data/groupdb/group_$groupid.php")){
		require_once Pcv(D_P."data/groupdb/group_$groupid.php");
	}else{
		require_once(D_P."data/groupdb/group_1.php");
	}
} else{
	require_once(D_P."data/groupdb/group_2.php");
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
		$footer_ad = $advertdb['footer'][array_rand($advertdb['footer'])]['code'] .'<br />';
	}
	unset($advertdb['header'],$advertdb['footer']);
}
if ($_SERVER['REQUEST_METHOD']=='POST' && strpos($REQUEST_URI,'login.php')===false && strpos($REQUEST_URI,'register.php')===false){
	$referer_a=parse_url($_SERVER['HTTP_REFERER']);
	$s_host=$_SERVER['HTTP_HOST'];
	strpos($s_host,':') && $s_host = substr($s_host,0,strpos($s_host,':'));
    if($referer_a['host'] && $referer_a['host']!=$s_host){
		Showmsg('undefined_action');
	}
}
function refreshto($URL,$content,$statime=1){
	global $db_ifjump;
	$URL=str_replace('&#61;','=',$URL);
	if($db_ifjump && $statime>0){
		ob_end_clean();
		global $tplpath,$fid,$imgpath,$db_obstart,$db_bbsname,$skin,$B_url;
		$index_name =& $db_bbsname;
		$index_url =& $B_url;
		$db_obstart == 1 && function_exists('ob_gzhandler') ? ob_start('ob_gzhandler') : ob_start();
		if(file_exists(R_P."data/style/$skin.php") && strpos($skin,'..')===false){
			include_once Pcv(R_P."data/style/$skin.php");
		}else{
			include_once(R_P."data/style/wind.php");
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
	global $db_obstart,$db_bbsurl,$db_htmifopen;
	if($db_htmifopen && strtolower(substr($URL,0,4))!='http'){
		$URL="$db_bbsurl/$URL";
	}
	ob_end_clean();
	if($db_obstart){
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
	//if(!$lang) $lang='N';
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
	setCookie(CookiePre().'_'.$ck_Var,$ck_Value,$ck_Time,$db_ckpath,$db_ckdomain,$S);
}
function GetCookie($Var){
    return $_COOKIE[CookiePre().'_'.$Var];
}
function CookiePre(){
	return substr(md5($GLOBALS['db_hash']),0,5);
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
	return @unlink($filename);
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
	global $db,$db_obstart,$db_footertime,$db_htmifopen,$P_S_T,$mtablewidth,$db_ceoconnect,$wind_version,$imgpath,$stylepath,$footer_ad,$db_union,$timestamp,$db_icp,$db_icpurl,$db_siteifopen,$advertdb;
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
	$db_icp && $db_icp = $db_icpurl ? "<a href=\"$db_icpurl\">$db_icp</a>" : "<a href=\"http://www.miibeian.gov.cn\">$db_icp</a>";
	require PrintEot('footer');
	if($advertdb['float'] || $advertdb['popup'] || $advertdb['leftfloat'] || $advertdb['rightfloat']){
		$leftfloat = $advertdb['leftfloat'][array_rand($advertdb['leftfloat'])];
		$rightfloat= $advertdb['rightfloat'][array_rand($advertdb['rightfloat'])];
		$floatAd = $advertdb['float'][array_rand($advertdb['float'])];
		$popupAd = $_COOKIE['hidepop'] ? '' : $advertdb['popup'][array_rand($advertdb['popup'])];
		require PrintEOT('advert');
	}
	$output = str_replace(array('<!--<!---->','<!---->'),array('',''),ob_get_contents());
	if(($db_siteifopen>1 || $db_siteifopen==1 && SCR=='read') && gethostbyname($_SERVER['HTTP_HOST'])!='127.0.0.1'){
		$output.="<script language=\"JavaScript\" src=\"http://ce.phpwind.com/default.php?type={$db_siteifopen}&siteid={$GLOBALS[db_sitehash]}\"></script>";
	}
	if($db_htmifopen){
		$output = preg_replace(
			"/\<a(\s*[^\>]+\s*)href\=([\"|\']?)([^\"\'>\s]+\.php\?[^\"\'>\s]+)([\"|\']?)/ies",
			"Htm_cv('\\3','<a\\1href=\"')",
			$output
		);
	}
	ob_end_clean();
	$db_obstart == 1 && function_exists('ob_gzhandler') ? ob_start('ob_gzhandler') : ob_start();
	echo $output;
	//flush;
	exit;
}
function Htm_cv($url,$tag){
	global $db_dir,$db_ext;
	if(ereg("^http|ftp|telnet|mms|rtsp|admin.php|rss.php",$url)===false){
		if(strpos($url,'#')!==false){
			$add = substr($url,strpos($url,'#'));
		}
		$url = str_replace(
			array('.php?','=','&',$add),
			array($db_dir,'-','-',''),
			$url
		).$db_ext.$add;
	}
	return stripslashes($tag).$url.'"';
}
function User_info(){
	global $db,$timestamp,$db_onlinetime,$winduid,$windpwd,$db_ifonlinetime,$c_oltime,$onlineip,$db_ipcheck,$tdtime,$montime;
	$ct = $sqladd = $sqltab = '';
	if(in_array(SCR,array('index','read','thread','post'))){
		$sqladd = SCR=='post' ? ",md.postcheck,sr.visit,sr.post,sr.reply" : ",sr.visit";
		$sqltab = "LEFT JOIN pw_singleright sr ON m.uid=sr.uid";
	}
	$detail = $db->get_one("SELECT m.uid,m.username,m.password,m.email,oicq,m.groupid,m.groups,m.memberid,m.regdate,m.timedf,m.style,m.datefm,m.t_num,m.p_num,m.yz,m.newpm,m.showsign,m.payemail,md.postnum,md.rvrc,md.money,md.credit,md.currency,md.lastvisit,md.thisvisit,md.onlinetime,md.lastpost,md.todaypost,md.monthpost,md.onlineip,md.uploadtime,md.uploadnum,md.editor,md.starttime $sqladd FROM pw_members m LEFT JOIN pw_memberdata md ON m.uid=md.uid $sqltab WHERE m.uid='$winduid'");
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
				$ct     .= ",onlinetime=onlinetime+'$c_oltime'";
				if($detail['lastpost']>$montime){
					$ct .= ",monoltime=monoltime+'$c_oltime'";
				}else{
					$ct .= ",monoltime='$c_ontime'";
				}
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
	global $db_charset;
	if($length && strlen($content)>$length){
		if($db_charset!='utf-8'){
			$retstr='';
			for($i = 0; $i < $length - 2; $i++) {
				$retstr .= ord($content[$i]) > 127 ? $content[$i].$content[++$i] : $content[$i];
			}
			return $retstr.' ..';
		}else{
			return utf8_trim(substr($content,0,$length)).' ..';
		}
	}
	return $content;
}
function utf8_trim($str) {
	$len = strlen($str);
	for($i=strlen($str)-1;$i>=0;$i-=1){
		$hex .= ' '.ord($str[$i]);
		$ch   = ord($str[$i]);
		if(($ch & 128)==0)	return substr($str,0,$i);
		if(($ch & 192)==192)return substr($str,0,$i);
	}
	return($str.$hex);
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
			if(!$v['endtime'] || $v['endtime'] < $timestamp){
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
function admincheck($forumadmin,$fupadmin,$username){
	if(!$username){
		return false;
	}
	if($forumadmin && strpos($forumadmin,",$username,")!==false){
		return true;
	}
	if($fupadmin && strpos($fupadmin,",$username,")!==false){
		return true;
	}
	return false;
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
function allowcheck($allowgroup,$groupid,$groups,$fid='',$allowforum=''){
	if(@strpos($allowgroup,','.$groupid.',')!==false){
		return true;
	}
	if($groups){
		$groupids=explode(',',substr($groups,1,-1));
		foreach($groupids as $key=>$val){
			if(@strpos($allowgroup,','.$val.',')!==false){
				return true;
			}
		}
	}
	if($fid && $allowforum && strpos(",$allowforum,",",$fid,")!==false){
		return true;
	}
	return false;
}
function geturl($attachurl,$type=''){
	global $attachdir,$attachpath,$db_ftpweb,$attach_url;

	if(file_exists($attachdir.'/'.$attachurl)){
		return array($attachpath.'/'.$attachurl,'Local');
	}
	if($db_ftpweb && !$attach_url){
		return array($db_ftpweb.'/'.$attachurl,'Ftp');
	}
	if(!$db_ftpweb && !is_array($attach_url)){
		return array($attach_url.'/'.$attachurl,'att');
	}
	if(!$db_ftpweb && count($attach_url)==1){
		return array($attach_url[0].'/'.$attachurl,'att');
	}
	if($type=='show'){
		return 'imgurl';
	}
	if($db_ftpweb && @$fp=fopen($db_ftpweb.'/'.$attachurl,'rb')){
		@fclose($fp);
		return array($db_ftpweb.'/'.$attachurl,'Ftp');
	}
	if($attach_url){
		foreach($attach_url as $key=>$val){
			if($val==$db_ftpweb)continue;
			if(@$fp=fopen($val.'/'.$attachurl,'rb')){
				@fclose($fp);
				return array($val.'/'.$attachurl,'att');
			}
		}
	}
	return false;
}
function randstr($lenth){
	mt_srand((double)microtime() * 1000000);
	for($i=0;$i<$lenth;$i++){
		$randval.= mt_rand(0,9);
	}
	$randval=substr(md5($randval),mt_rand(0,32-$lenth),$lenth);
	return $randval;
}
function num_rand($lenth){
	mt_srand((double)microtime() * 1000000);
	for($i=0;$i<$lenth;$i++){
		$randval.= mt_rand(0,9);
	}
	return $randval;
}
function PwStrtoTime($time){
	global $db_timedf;
	return function_exists('date_default_timezone_set') ? strtotime($time) - $db_timedf*3600 : strtotime($time);
}
function Pcv($filename,$ifcheck=1){
	strpos($filename,'http://')!==false && exit('Forbidden');
	$ifcheck && strpos($filename,'..')!==false && exit('Forbidden');
	return $filename;
}
function GetPtable($tbid,$tid=''){
	if($GLOBALS['db_plist'] && $tbid=='N' && $tid){
		@extract($GLOBALS['db']->get_one("SELECT ptable AS tbid FROM pw_threads WHERE tid='$tid'"));		
	}
	if($GLOBALS['db_plist'] && $tbid && is_numeric($tbid) && strpos(",{$GLOBALS[db_plist]},",",$tbid,")!==false){
		return  'pw_posts'.$tbid;
	}else{
		return 'pw_posts';
	}
}
?>