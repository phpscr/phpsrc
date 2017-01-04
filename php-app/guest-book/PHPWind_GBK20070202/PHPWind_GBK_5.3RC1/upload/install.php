<?php
/***************************************************
*	install.php - installation of PHPwind		   *
*	Author: Fengyu,Yuling						   *
*	PHPwind (http://www.phpwind.net)			   *
***************************************************/

error_reporting(E_ERROR | E_PARSE);

@set_time_limit(0);
set_magic_quotes_runtime(0);
if(!@ini_get('register_globals') || !get_magic_quotes_gpc()){
	@extract($_POST,EXTR_SKIP);
	@extract($_GET,EXTR_SKIP);
}
!$_POST && $_POST=array();
!$_GET && $_GET=array();
foreach($_POST as $_key=>$_value){
	!ereg("^\_",$_key) && $$_key=$_POST[$_key];
}
foreach($_GET as $_key=>$_value){
	!ereg("^\_",$_key) && $$_key=$_GET[$_key];
}
eval('$__file__=__FILE__;');
define('D_P',$__file__ ? dirname($__file__).'/' :	'./');
define('R_P',D_P);
include(R_P.'lang/install_lang.php');
$basename="install.php";

if(!$step){
	$wind_licence= readover(R_P.'licence.txt');
	$wind_licence = str_replace('  ', '&nbsp; ', nl2br($wind_licence));
	include(R_P.'install_htm.php');exit;
} elseif($step==2){

	$language='wind';
	//$error=language($language);

	include(D_P.'data/sql_config.php');
	$check=1;
	$correct='......<font class=r>OK</font>';
	$incorrect=$lang['777_test'];
	$uncorrect=$lang['no_file'];
	$w_check=array(
		'data',
		'data/sql_config.php',
		'data/bbscache',
		'data/groupdb',
		'data/style',
		'attachment',
		'attachment/upload',
		'attachment/photo',
		'htm_data',
		'template',
		'template/wind',
		'template/admin',
		'images/cn_img'
	);

	if($fp=@fopen(R_P.'test.txt',"wb")){
		$state=$correct;
		fclose($fp);
	} else{
		$state=$lang['777_test'].$lang['no_write'];
	}
	$count=count($w_check);
	for($i=0; $i<$count; $i++){
		if(!file_exists(R_P.$w_check[$i])){
			$w_check[$i].= $uncorrect;$check=0;
		} elseif(is_writable(R_P.$w_check[$i])){
			$w_check[$i].= $correct;
		} else{
			$w_check[$i].=$incorrect; $check=0; 
		}
	}
	$check && @unlink(R_P.'test.txt');
	include('install_htm.php');exit;
} elseif($step==3){
	$check=1;
	if(!$password || $password != $password_check) {
		$check=0;
	}
	if($check){
		include(D_P.'data/sql_config.php');
		$showpwd=$password;
		$writepassword=md5($password);
		$charset=str_replace('-','',$lang['db_charset']);
		$writetofile=
"<?php
/**
* $lang[info]
*/
\$dbhost = '$SERVER';	// $lang[dbhost]
\$dbuser = '$SQLUSER';	// $lang[dbuser]
\$dbpw = '$SQLPASSWORD';	// $lang[dbpw]
\$dbname = '$SQLNAME';	// $lang[dbname]
\$database = 'mysql';	// $lang[database]
\$PW = '$SQLZUI';	//$lang[PW]
\$pconnect = 0;	//$lang[pconnect]

/*
$lang[charset]
*/
\$charset='$charset';

/**
* $lang[ma_info]
*/
\$manager='$INSTALL_NAME';	//$lang[manager_name]
\$manager_pwd='$writepassword';	//$lang[manager_pwd]

/**
* $lang[hostweb]
*/
\$db_hostweb=1;			//$lang[ifhostweb]

/*
* $lang[attach_url]
*/
\$attach_url=array();

/*
* $lang[pic_att]
*/
\$picpath='images';
\$attachname='attachment';

/**
* $lang[hackdb]
*/
\$db_hackdb=array(	
	'bank'=>array('$lang[bank]','bank','1'),
	'colony'=>array('$lang[colony]','colony','1'),
	'advert'=>array('$lang[advert]','advert','0'),
	'new'=>array('$lang[newadmin]','new','0'),
	'medal'=>array('$lang[medal]','medal','0'),
	'toolcenter'=>array('$lang[toolcenter]','toolcenter','0'),
	'blog'=>array('$lang[blog]','blog','0'),
	'invite'=>array('$lang[invite]','invite','0'),
	'passport'=>array('$lang[passport]','passport','0'),
	'team'=>array('$lang[team]','team','0'),
	'nav'=>array('$lang[nav]','nav','0')
);
".'?>';
		writeover(D_P.'data/sql_config.php',$writetofile);
	}
	include(D_P.'data/sql_config.php');
	include(R_P.'require/db_'.$database.'.php');
	$db = new DB($dbhost, $dbuser, $dbpw, '', $pconnect);
	if(!@mysql_select_db($dbname)) {
		if(mysql_get_server_info() > '4.1' && $charset){
			mysql_query("CREATE DATABASE $dbname DEFAULT CHARACTER SET $charset");
		}else{
			mysql_query("CREATE DATABASE $dbname");
		}
		mysql_error() && exit($lang['no_database']);
	}
	mysql_select_db($dbname);
	$query=$db->query("SHOW TABLES LIKE '".$PW."members'");
	while($TABLE=$db->fetch_array($query,MYSQL_NUM)){
		$D_exists=$TABLE[0]==$PW.'members' ? 1 : 0;
	}
	$lang['have_install']=str_replace('$dbname',$dbname,$lang['have_install']);
	include(D_P.'install_htm.php');exit;
} elseif($step==4){
	include(D_P.'data/sql_config.php');
	include(R_P.'require/db_'.$database.'.php');
	include(R_P.'admin/cache.php');

	$db = new DB($dbhost, $dbuser, $dbpw, '', $pconnect);
	if(!@mysql_select_db($dbname)) {
		mysql_query("CREATE DATABASE $dbname");

		mysql_error() && exit($lang['no_database']);
	}
	mysql_select_db($dbname);
	$content=readover(R_P."lang/install_wind.sql");
	$content=preg_replace("/{#(.+?)}/eis",'$lang[\\1]',$content);
	creat_table($content);
	$timestamp	= time();
	$t			= getdate($timestamp+8*3600);
	$tdtime		= (floor($timestamp/3600)-$t['hours'])*3600;
	$writepwd=md5($password);
	$db->update("INSERT INTO pw_members (username, password, email,publicmail, groupid,memberid, icon, gender,regdate,receivemail) VALUES ('$manager', '$manager_pwd', '$adminemail', '1', '3','8', '', '1', '$timestamp','1')");
	$uid=$db->insert_id();
	$db->update("INSERT INTO pw_memberdata (uid,lastvisit,thisvisit) VALUES ('$uid','$timestamp','$timestamp')");
	
	$db->update("UPDATE pw_bbsinfo SET newmember='$manager',tdtcontrol='$tdtime',totalmember=totalmember+1 WHERE id='1'");

	$db_siteid      = generatestr(16);
	$db_siteownerid = generatestr(18);
	$db_sitehash = '10'.SitStrCode(md5($db_siteid.$db_siteownerid),md5($db_siteownerid.$db_siteid));
	$siteifopen = 0;
	$ifopen_t && $siteifopen += 1;
	$ifopen_s && $siteifopen += 2;
	$db->update("REPLACE INTO pw_config(db_name,db_value) VALUES ('db_siteid','$db_siteid')");
	$db->update("REPLACE INTO pw_config(db_name,db_value) VALUES ('db_siteownerid','$db_siteownerid')");
	$db->update("REPLACE INTO pw_config(db_name,db_value) VALUES ('db_sitehash','$db_sitehash')");
	$db->update("REPLACE INTO pw_config(db_name,db_value) VALUES('db_siteifopen','$siteifopen')");

	if(!($REQUEST_URI=$_SERVER['REQUEST_URI'])){
		$REQUEST_URI=$_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
	}
	$wwwurl=$bbsurl='http://'.$_SERVER['HTTP_HOST'].substr($REQUEST_URI,0,strrpos($REQUEST_URI,'/'));
	include('install_htm.php');exit;
} elseif($step==5){
	include(D_P.'data/sql_config.php');
	include(R_P.'require/db_'.$database.'.php');
	include(R_P.'admin/cache.php');
	
	$db = new DB($dbhost, $dbuser, $dbpw, '', $pconnect);
	mysql_select_db($dbname);
	$db_hash=confuse();
	$db->update("UPDATE pw_config SET db_value='$db_hash' WHERE db_name='db_hash'");
	$db->update("UPDATE pw_config SET db_value='$bbsurl' WHERE db_name='db_bbsurl'");
	$db->update("UPDATE pw_config SET db_value='$wwwurl' WHERE db_name='db_wwwurl'");
	writefile();
	updatecache();
	if(!is_writeable(R_P.'install.php')){
		$unlinkerror='<tr><td align=left class=c align=middle colSpan=2>&nbsp;&nbsp;&nbsp;&nbsp;'.$lang['del_install'].'</td></tr>';
	}
	include(R_P.'install_htm.php');
	if (@unlink(R_P.'install.php')){
		@unlink(R_P.'install_htm.php');
	}
	exit;
}
function creat_table($content) {
	global $db,$installinfo,$PW,$lang,$charset;
	
	$sql=explode("\n",$content);
	$query='';
	foreach($sql as $key => $value){
		$value=trim($value);
		if(!$value || $value[0]=='#') continue;
		if(eregi("\;$",$value)){
			$query.=$value;
			if(eregi("^CREATE",$query)){
				$name=substr($query,13,strpos($query,'(')-13);
				$c_name=str_replace('pw_',$PW,$name);
				$installinfo.='<font color="#0000EE">'.$lang['creat_table'].'</font>'.$c_name.' ... <font color="#0000EE">'.$lang['success'].'</font><br>';

				$extra = substr(strrchr($query,')'),1);
				$tabtype = substr(strchr($extra,'='),1);
				$tabtype = substr($tabtype, 0, strpos($tabtype,strpos($extra,' ') ? ' ' : ';'));

				$query = str_replace($extra,'',$query);
				if($db->server_info() > '4.1'){
					$extra = $charset ? "ENGINE=$tabtype DEFAULT CHARSET=$charset;" : "ENGINE=$tabtype;";
				}else{
					$extra = "TYPE=$tabtype;";
				}
				$query .= $extra;
			}
			$db->query($query);
			$query='';
		} else{
			$query.=$value;
		}
	}
}

function confuse(){
	$rand='0123%^&*45ICV%^&*B6789qazw~!@#$sxedcrikolpQWER%^&*TYUNM';
	mt_srand((double)microtime() * 1000000);
	for($i=0;$i<10;$i++){
		$code.=$rand[mt_rand(0,strlen($rand))];
	}
	return $code;
}

function writefile(){
	global $lang;
	$writeinto=str_pad("<?die;?>",96)."\n";
	writeover(D_P.'data/bbscache/online.php',$writeinto);
	writeover(D_P.'data/bbscache/guest.php',$writeinto);
	writeover(D_P.'data/bbscache/olcache.php',"<?php\n\$userinbbs=1;\n\$guestinbbs=0;\n?>");
}

function readover($filename,$method="rb"){
	if($handle=@fopen($filename,$method)){
		flock($handle,LOCK_SH);
		$filedata=@fread($handle,filesize($filename));
		fclose($handle);
	}
	return $filedata;
}
function writeover($filename,$data,$method="rb+"){
	@touch($filename);
	if($handle=@fopen($filename,$method)){
		flock($handle,LOCK_EX);
		fputs($handle,$data);
		if($method=="rb+") ftruncate($handle,strlen($data));
		fclose($handle);
	}
}
function GetLang($lang,$EXT="php"){
	return "./template/admin/cp_lang_$lang.$EXT";
}
function adminmsg(){
}
function language($language,$tplpath=''){
	if($tplpath){
		$tpl_w=$tplpath;
		$tpl_a='cp_'.$tplpath;
	} else{
		$tpl_w='wind';
		$tpl_a='admin';
	}
	$error=0;
	if(!is_dir(R_P."lang/$language")){
		$error=1;
	}
	if(!is_dir(R_P."template/$tpl_w")){
		if(!@mkdir("./template/$tpl_w")){
			$error=2;
		}
		@chmod(R_P."template/$tpl_w",0777);
	}
	if(!is_dir(R_P."template/$tpl_a")){
		if(!@mkdir(R_P."template/$tpl_a")){
			$error=2;
		}
		@chmod(R_P."template/$tpl_a",0777);
	}
	$lang=array(
				'lang_action.php',		'lang_bbscode.php',
				'lang_email.php',		'lang_masigle.php',
				'lang_msg.php',			'lang_post.php',
				'lang_refreshto.php',	'lang_sort.php',
				'lang_toollog.php',		'lang_log.php',
				'lang_writemsg.php',	'lang_wap.php',
			);
	$cp_lang=array(
				'cp_lang_all.php',		'cp_lang_cpmsg.php',
				'cp_lang_left.php',		'cp_lang_rightset.php',
			);
	foreach($lang as $key=>$value){
		writeover(R_P."template/$tpl_w/$value",readover(R_P."lang/$language/$value"));
		@chmod(R_P."template/$tpl_w/$value",0777);
	}
	foreach($cp_lang as $key=>$value){
		writeover(R_P."template/$tpl_a/$value",readover(R_P."lang/$language/$value"));
		@chmod(R_P."template/$tpl_a/$value",0777);
	}

	include_once(R_P."lang/$language/all_lang.php");
	$dir=opendir(R_P."lang/$language/wind/");
	while($file=readdir($dir)){
		if(eregi("\.htm$",$file)){
			$content=readover(R_P."lang/$language/wind/$file");
			$content=preg_replace("/{#(.+?)}/eis",'$lang[\\1]',$content);
			writeover(R_P."template/$tpl_w/$file",$content);
			@chmod(R_P."template/$tpl_w/$file",0777);
		}
	}

	$dir=opendir(R_P."lang/$language/admin/");
	while($file=readdir($dir)){
		if(eregi("\.htm$",$file)){
			$content=readover(R_P."lang/$language/admin/$file");
			$content=preg_replace("/{#(.+?)}/eis",'$lang[\\1]',$content);
			writeover(R_P."template/$tpl_a/$file",$content);
			@chmod(R_P."template/$tpl_a/$file",0777);
		}
	}

	$dir=opendir(R_P."lang/$language/");
	while($file=readdir($dir)){
		if(eregi("\.js$",$file)){
			$content=readover(R_P."lang/$language/$file");
			$content=preg_replace("/{#(.+?)}/eis",'$lang[\\1]',$content);
			writeover(R_P."data/$file",$content);
			@chmod(R_P."data/$file",0777);
		}
	}
	return $error;
}
function Pcv($filename,$ifcheck=1){
	strpos($filename,'http://')!==false && exit('Forbidden');
	$ifcheck && strpos($filename,'..')!==false && exit('Forbidden');
	return $filename;
}
function generatestr($len) {
	mt_srand((double)microtime() * 1000000);
    $keychars = "abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWYXZ";
	$maxlen = strlen($keychars)-1;
	$str = '';
	for ($i=0;$i<$len;$i++){
		$str .= $keychars[mt_rand(0,$maxlen)];
	}
	return substr(md5($str.time().$_SERVER["HTTP_USER_AGENT"].$GLOBALS['db_hash']),0,$len);
}
function SitStrCode($string,$key,$action='ENCODE'){
	$string	= $action == 'ENCODE' ? $string : base64_decode($string);
	$len	= strlen($key);
	$code	= '';
	for($i=0; $i<strlen($string); $i++){
		$k		= $i % $len;
		$code  .= $string[$i] ^ $key[$k];
	}
	$code = $action == 'DECODE' ? $code : str_replace('=','',base64_encode($code));
	return $code;
}
?>