<?php
/**
*
*  Copyright (c) 2003-06  PHPWind.net. All rights reserved.
*  Support : http://www.phpwind.net
*  This software is the proprietary information of PHPWind.com.
*
*/
!defined('R_P') && exit('Forbidden');

unset($_ENV,$HTTP_ENV_VARS,$_REQUEST,$HTTP_POST_VARS,$HTTP_GET_VARS,$HTTP_POST_FILES,$HTTP_COOKIE_VARS);
if(!get_magic_quotes_gpc()){
	Add_S($_POST);
	Add_S($_GET);
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
$timestamp  = time();
require_once(R_P.'admin/defend.php');
$db_cvtime != 0 && $timestamp += $db_cvtime*60;

$cookietime = $timestamp+31536000;
!$_SERVER['PHP_SELF'] && $_SERVER['PHP_SELF']=$_SERVER['SCRIPT_NAME'];
$REQUEST_URI  = $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
//unset($db_ckpath,$db_ckdomain);

$wind_version = "4.3.2";
$wind_repair  = '20060212';
$db_olsize    = 96;
$htmdir       = 'htm_data';

list($db_moneyname,$db_moneyunit,$db_rvrcname,$db_rvrcunit,$db_creditname,$db_creditunit)=explode("\t",$db_credits);

if($adminjob=='quit'){
	Cookie('AdminUser','',0);
	ObHeader($admin_file);
}

include_once(D_P.'data/bbscache/dbset.php');

$imgpath    = $db_http      != 'N' ? $db_http     : $picpath;
$attachpath = $db_attachurl != 'N' ? $db_attachurl : $attachname;
$imgdir     = R_P.$picpath;
$attachdir  = R_P.$attachname;

if(D_P != R_P && $db_http != 'N'){
	$R_url=substr($db_http,-1)=='/' ?  substr($db_http,0,-1) : $db_http;
	$R_url=substr($R_url,0,strrpos($R_url,'/'));
}else{
	$R_url=$db_bbsurl;
}

if(!$adminjob || $adminjob=='left' || $adminjob=='admin' || $adminjob=='settings' || $adminjob=='creathtm') $ob_check=1;/*解决打开 ob_gzhandler 进后台出现下载问题*/
!$ob_check && $db_obstart==1 ? ob_start('ob_gzhandler') : ob_start();

$skin=$db_defaultstyle;
$skinco	   = GetCookie('skinco');
$_GET['skinco'] && $skinco=$_GET['skinco'];
$_POST['skinco'] && $skinco=$_POST['skinco'];
if($skinco && file_exists(D_P."data/style/$skinco.php") && strpos($skinco,'..')===false){
	$skin=$skinco;
	Cookie('skinco',$skinco);
}

include_once(D_P."data/bbscache/level.php");
include_once(D_P."data/bbscache/forum_cache.php");
@include_once(D_P."data/style/$skin.php");
include_once(D_P."data/sql_config.php");
include_once(R_P.'require/db_'.$database.'.php');
include_once(R_P.'admin/cache.php');

$H_url=$db_wwwurl;
$B_url=$db_bbsurl;

$bbsrecordfile=D_P."data/bbscache/admin_record.php";
$F_count=F_L_count($bbsrecordfile,2000);
$L_T=1200-($timestamp-@filemtime($bbsrecordfile));
$L_left=15-$F_count;

if($F_count>15 && $L_T>0){
	require_once GetLang('cpmsg');
	$msg=$lang['login_fail'];
	include PrintEot('adminlogin');exit;
}

/**
* 数据库连接
*/
$db = new DB($dbhost, $dbuser, $dbpw, $dbname, $pconnect);
unset($dbhost, $dbuser, $dbpw, $dbname, $pconnect);

if (file_exists("install.php")){
	adminmsg('installfile_exists');
}
if(!$manager){
	include_once PrintEot('unloginleft');
	adminmsg('sql_config');
}
if($_POST['admin_pwd'] && $_POST['admin_name']){
	$pwuser		= $_POST['admin_name'];
	$AdminUser	= StrCode($timestamp."\t".$pwuser."\t".md5(PwdCode(md5($_POST['admin_pwd'])).$timestamp));
	Cookie('AdminUser',$AdminUser);
}elseif(GetCookie('AdminUser')){
	$AdminUser = GetCookie('AdminUser');
}else{
	$AdminUser = '';
}

list(,,,,,$admingd) = explode("\t",$db_gdcheck);
if($AdminUser){
	$CK			= explode("\t",StrCode($AdminUser,'DECODE'));
	$admin_name = stripcslashes($CK[1]);
}else{
	$CK = $admin_name = '';
}
$rightset	 = checkpass($CK);
$admin_gid	 = $rightset['gid'];
$admin_level = If_manager ? 'manager' : $ltitle[$admin_gid];
if(!If_manager){
	Iplimit();
	CheckVar($_POST);
	CheckVar($_GET);
}
if (!$rightset) {
	if ($_POST['admin_name'] && $_POST['admin_pwd']){
		$record_name= str_replace('|','&#124;',Char_cv($_POST['admin_name']));
		$record_pwd	= str_replace('|','&#124;',Char_cv($_POST['admin_pwd']));
		$new_record="<?die;?>|$record_name|$record_pwd|Logging Failed|$onlineip|$timestamp|\n";
		writeover($bbsrecordfile,$new_record,"ab");
		adminmsg('login_error');
	}
	include PrintEot('adminlogin');exit;
}elseif($_POST['admin_name']){
	ObHeader($REQUEST_URI);
}

$_postdata	 = $_POST ? PostLog($_POST) : '';
$record_name = str_replace('|','&#124;',Char_cv($admin_name));
$record_URI	 = str_replace('|','&#124;',Char_cv($REQUEST_URI));
$new_record="<?die;?>|$record_name||$record_URI|$onlineip|$timestamp|$_postdata|\n";
writeover($bbsrecordfile,$new_record,"ab");

function Cookie($ck_Var,$ck_Value,$ck_Time='F'){
	global $cookietime,$db_ckpath,$db_ckdomain;
	if($ck_Time=='F') $ck_Time = $cookietime;
	$S=$_SERVER['SERVER_PORT']=='443' ? 1:0;
	!$db_ckpath && $db_ckpath='/';
	setCookie($ck_Var,$ck_Value,$ck_Time,$db_ckpath,$db_ckdomain,$S);
}
function GetCookie($Var){
    return $_COOKIE[$Var];
}
function Add_S(&$array){
	if($array){
		foreach($array as $key=>$value){
			if(!is_array($value)){
				$array[$key]=addslashes($value);
			}else{
				Add_S($array[$key]);
			}
		}
	}
}
function HtmlConvert(&$array){
	if(is_array($array)){
		foreach($array as $key => $value){
			if(!is_array($value)){
				$array[$key]=htmlspecialchars($value);
			}else{
				HtmlConvert($array[$key]);
			}
		}
	} else{
		$array=htmlspecialchars($array);
	}
}
function substrs($content,$length) {
	if($length && strlen($content)>$length){
		$num=0;
		for($i=0;$i<$length-3;$i++) {
			if(ord($content[$i])>127){
				$num++;
			}
		}
		$num%2==1 ? $content=substr($content,0,$length-4):$content=substr($content,0,$length-3);
		$content.='..';
	}
	return $content;
}
function checkpass($CK){
	global $db,$manager,$manager_pwd,$lg_num,$admingd,$onlineip;
	if (!$CK){
		return false;
	}
	if($_POST['Login_f']==1 && $admingd){
		if(!GdConfirm($lg_num)){
			global $basename,$admin_file;
			Cookie('AdminUser','',0);
			$basename = $admin_file;
			adminmsg('check_error');
		}
	}
	if(strtolower($CK[1]) == strtolower($manager)){
		if(!SafeCheck($CK,PwdCode($manager_pwd))){
			$rt = $db->get_one("SELECT password FROM pw_members WHERE username='$CK[1]'");
			if(!SafeCheck($CK,PwdCode($rt['password']))){
				return false;
			}
		}
		define('If_manager',1);
		$rightset		 = array();
		$rightset['gid'] = 3;
		require GetLang('left');
		foreach($lang as $key=>$left){
			foreach($left as $key=>$value){
				$rightset[$key] = '1';
			}
		}
		return $rightset;
	} else{
		define('If_manager',0);
		$admindb = $db->get_one("SELECT m.password,m.groupid,u.gptype,u.allowadmincp FROM pw_members m LEFT JOIN pw_usergroups u ON u.gid=m.groupid WHERE username='$CK[1]'");
		if(!SafeCheck($CK,PwdCode($admindb['password']))){
			return false;
		}
	}
	if(!$admindb){
		return false;
	}
	$rightset = array();
	if(($admindb['gptype']=='system' || $admindb['gptype']=='special') && $admindb['allowadmincp']){
		$rightset = $db->get_one("SELECT * FROM pw_adminset WHERE gid='$admindb[groupid]'");
		if(!$rightset){
			$rightset = array('gid'=>'$admindb[groupid]');
		} else{
			$rightset = P_unserialize($rightset['value']);
			$rightset['gid'] = $admindb['groupid'];
		}
		return $rightset;
	} else{
		return false;
	}
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
function gets($filename,$value)
{
	if($handle=@fopen($filename,"rb")){
		flock($handle,LOCK_SH);
		$getcontent=fread($handle,$value);//fgets调试
		fclose($handle);
	}
	return $getcontent;
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
function openfile($filename,$style='Y')
{
	if($style=='Y'){
		$filedata=readover($filename);
		$filedata=str_replace("\n","\n<:wind:>",$filedata);
		$filedb=explode("<:wind:>",$filedata);
		//array_pop($filedb);
		$count=count($filedb);
		if($filedb[$count-1]==''||$filedb[$count-1]=="\r"){unset($filedb[$count-1]);}
		if(empty($filedb)){$filedb[0]="";}
		return $filedb;
	}else{
		$filedb=file($filename);
		return $filedb;
	}
}
function adminmsg($msg,$jumpurl='',$t=2)
{
	extract($GLOBALS, EXTR_SKIP);
	!$basename && $basename=$REQUEST_URI;
	if($jumpurl!=''){
		$ifjump="<META HTTP-EQUIV='Refresh' CONTENT='$t; URL=$jumpurl'>";
	}
	require_once GetLang('cpmsg');
	$lang[$msg] && $msg=$lang[$msg];
	include PrintEot('message');exit;
}
function Char_cv($msg){
	$msg = str_replace("\t","",$msg);
	$msg = str_replace("<","&lt;",$msg);  
	$msg = str_replace(">","&gt;",$msg);
	$msg = str_replace("\r","",$msg);
	$msg = str_replace("\n","<br />",$msg);
	$msg = str_replace("   "," &nbsp; ",$msg);#编辑时比较有效
	return $msg;
}
function ieconvert($msg){
	$msg = str_replace("\t","",$msg);
	$msg = str_replace("\r","",$msg);
	$msg = str_replace("   "," &nbsp; ",$msg);#编辑时比较有效
	return $msg;
}
function Quot_cv($msg){
	$msg = str_replace('"','&quot;',$msg);
	return $msg;       
}
function deldir($path){
	if (file_exists($path)){
		if(is_file($path)){
			P_unlink($path);
		} else{
			$handle = opendir($path);
			while ($file = readdir($handle)) {
				if (($file!=".") && ($file!="..") && ($file!="")){
					if (is_dir("$path/$file")){
						deldir("$path/$file");
					} else{
						P_unlink("$path/$file");
					}
				}
			}
			closedir($handle);
			rmdir($path);
		}
	}
}
function getusergroup($username,$getpostnum=N){
	global $db;
	include D_P."data/bbscache/level.php";
	@extract($db->get_one("SELECT m.groupid,md.postnum FROM pw_members m LEFT JOIN pw_memberdata md ON md.uid=m.uid WHERE m.username='$username'"));
	if(ereg("^[0-9]{1,}",$groupid) || $getpostnum==Y){
		$count=count($lpost);
		for($i=0;$i<$count;$i++){
			if($postnum>=$lpost[$i] && $postnum<$lpost[$i+1]){
				$groupid=$i;$isalet=1;
				break;

			}
		}
		if(!$isalet) $groupid=0;
	}
	settype($groupid, "string");
	return $groupid;
}
function ifadmin($username){
	global $db;
	$query=$db->query("SELECT forumadmin FROM pw_forums WHERE type<>'category'");
	while($forum=$db->fetch_array($query)){
		if(strpos($forum['forumadmin'],','.$username.',')!==false){
			return true;
		}
	}
	return false;
}
function ifcheck($var,$out){
	global ${$out.'_Y'},${$out.'_N'};
	if($var) ${$out.'_Y'}="CHECKED"; else ${$out.'_N'}="CHECKED";

}
function F_L_count($filename,$offset)
{
	global $onlineip;
	$count=0;
	if($fp=@fopen($filename,"rb")){
		flock($fp,LOCK_SH);
		fseek($fp,-$offset,SEEK_END);
		$readb=fread($fp,$offset);
		fclose($fp);
		$readb=trim($readb);
		$readb=explode("\n",$readb);
		$count=count($readb);$count_F=0;
		for($i=$count-1;$i>0;$i--){
			if(strpos($readb[$i],"|Logging Failed|$onlineip|")===false){
				break;
			}
			$count_F++;
		}
	}
	return $count_F;
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
function GetLang($lang,$EXT="php"){
	global $tplpath;
	$adminpath=file_exists(R_P."template/admin_$tplpath") ? "admin_$tplpath" : 'admin';
	if($lang == 'email' || $lang == 'log' || $lang == 'bbscode'){
		$path=R_P."template/$tplpath/lang_$lang.$EXT";
		!file_exists($path) && $path=R_P."template/wind/lang_$lang.$EXT";
		return $path;
	} else{
		$path=R_P."template/$adminpath/cp_lang_$lang.$EXT";
	}
	return $path;
}
function PrintEot($template,$EXT="htm")
{
	global $tplpath;

	///cms
	if($template=='bbscode' || $template=='c_header' || $template=='c_footer'){
		$path=R_P."template/$tplpath/$template.$EXT";
		!file_exists($path) && $path=R_P."template/wind/$template.$EXT";
		return $path;
	}
	///cms
	$adminpath=file_exists(R_P."template/admin_$tplpath") ? "admin_$tplpath" : 'admin';
	if(!$template) $template='N';
	$path=R_P."template/$adminpath/$template.$EXT";
	return $path;
}
function readlog($filename,$offset=1024000)
{
	$readb=array();
	if($fp=@fopen($filename,"rb")){
		flock($fp,LOCK_SH);
		$size=filesize($filename);
		$size>$offset ? fseek($fp,-$offset,SEEK_END): $offset=$size;
		$readb=fread($fp,$offset);
		fclose($fp);
		$readb=str_replace("\n","\n<:wind:>",$readb);
		$readb=explode("<:wind:>",$readb);
		$count=count($readb);
		if($readb[$count-1]==''||$readb[$count-1]=="\r"){unset($readb[$count-1]);}
		if(empty($readb)){$readb[0]="";}
	}
	return $readb;
}

function checkselid($selid){
	if(is_array($selid)){
		$ret='';
		foreach($selid as $key => $value){
			if(!is_numeric($value)){
				return false;
			}
			$ret .= $ret ? ','.$value : $value;
		}
		return $ret;
	} else{
		return '';
	}
}
function ObHeader($URL){
	echo "<meta http-equiv='refresh' content='0;url=$URL'>";exit;
}
function updateadmin(){
	global $db;
	$query=$db->query("SELECT forumadmin FROM pw_forums");
	while($forum=$db->fetch_array($query)){
		$adminarray=explode(",",addslashes($forum['forumadmin']));
		foreach($adminarray as $key=>$value){
			$value=trim($value);
			if($value){
				$f_admin[]=$value;
			}
		}
	}
	$f_admin=array_unique($f_admin);

	$query=$db->query("SELECT uid,username,groupid,groups FROM pw_members WHERE groupid=5 OR groups LIKE '%,5,%'");
	while($rt=$db->fetch_array($query)){
		if(!in_array($rt['username'],$f_admin)){
			if($rt['groupid']=='5'){
				$db->update("UPDATE pw_members SET groupid='-1' WHERE uid='$rt[uid]'");
			}else{
				$groups=str_replace(',5,','',$rt['groups']);
				$db->update("UPDATE pw_members SET groups='$groups' WHERE uid='$rt[uid]'");
			}
		}
	}

	foreach($f_admin as $key => $value){
		$rt=$db->get_one("SELECT uid,username,groupid,groups FROM pw_members WHERE username='$value'");
		if($rt['uid']){
			if($rt['groupid']=='-1'){
				$db->update("UPDATE pw_members SET groupid='5' WHERE uid='$rt[uid]'");
			} elseif($rt['groupid']!='5' && strpos($rt['groups'],',5,')===false){
				$groups=$rt['groups'] ? $rt['groups'].'5,' : ",5,";
				$db->update("UPDATE pw_members SET groups='$groups' WHERE uid='$rt[uid]'");
			}
		}
	}
}

function GetAllowForum($username){
	global $db;
	$allowfid    = 0;
	$forumoption = '';
	$query = $db->query("SELECT fid,name,forumadmin FROM pw_forums WHERE type!='category' AND forumadmin LIKE '%,$username,%'");
	while($rt = $db->fetch_array($query)){
		$allowfid    .= ','.$rt['fid'];
		$forumoption .= "<option value='$rt[fid]'> >> $rt[name]</option>";
	}
	return array($allowfid,$forumoption);
}
function GetHiddenForum(){
	global $db;
	$forumoption = '<option></option>';
	$allowfid    = 0;
	$query = $db->query("SELECT fid,name FROM pw_forums WHERE f_type='hidden'");
	while ($rt = $db->fetch_array($query)){
		$allowfid    .= ','.$rt['fid'];
		$forumoption .= "<option value=\"$rt[fid]\"> &nbsp;|- $rt[name]</option>";
	}
	return array($allowfid,$forumoption);
}
function Iplimit(){
	global $db_iplimit,$onlineip;
	if($db_iplimit){
		$ip_a=explode(",",$db_iplimit);
		foreach($ip_a as $k=>$v){
			if(!$v)continue;
			$v=trim($v);
			if(strpos(','.$onlineip.'.',','.$v.'.')===false){
				adminmsg('ip_ban');
			}
		}
	}
}
function CheckVar($var){
	if(is_array($var)){
		foreach($var as $key=>$value){
			if(!in_array($_key,array('advert','newadvert'))){
				CheckVar($value);
			}
		}
	}else{
		$tar = array('<iframe','<meta','<script');
		foreach($tar as $k=>$v){
			if(strpos(strtolower($var),$v)!==false){
				global $basename;
				$basename="javascript:history.go(-1);";
				adminmsg('word_error');				
			}
		}
	}
}
function PostLog($log){
	foreach($log as $key=>$val){
		if(is_array($val)){
			$data .= "$key=array(".PostLog($val).")";
		}else{
			$val = str_replace(array("\n","\r","|"),array('','','&#124;'),$val);
			$data .= "$key=$val, ";
		}
	}
	return $data;
}
function GdConfirm($code){
	Cookie('cknum','',0);
	if(!$code || !SafeCheck(explode("\t",StrCode(GetCookie('cknum'),'DECODE')),$code,'cknum',300)){
		return false;
	}else{
		return true;
	}
}
function GetModulePath($SCR){
	global $db,$timestamp,$fid,$db_cmsper;
	if($SCR == 'index'){
		$cachefile = D_P.'data/bbscache/c_module_'.$SCR.'php';
	}elseif($SCR == 'read'){
		$cachefile = D_P.'data/bbscache/c_module_thread_'.(int)$fid.'.php';
	}else{
		$cachefile = D_P.'data/bbscache/c_module_'.$SCR.'_'.(int)$fid.'.php';
	}
	if(!file_exists($cachefile) || $timestamp - filemtime($cachefile) > $db_cmsper*60){
		require(R_P.'require/c_module_func.php');
	}
	return $cachefile;
}
?>