<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=code";
if($_GET['sessionid']){
	Cookie('sessionid',$sessionid);
}else{
	$sessionid=GetCookie('sessionid');
}
if(!$action){
	switch ($db_siteifopen){
		case 0:
			$ifopen_t=$ifopen_s='';
			break;
		case 1:
			$ifopen_t='checked';
			$ifopen_s='';
			break;
		case 2:
			$ifopen_t='';
			$ifopen_s='checked';
			break;
		default :
			$ifopen_t=$ifopen_s='checked';
	}
	include PrintEot('code');exit;
}elseif($_POST['action']=='submit'){
	if($ifopen_t || $ifopen_s){
		if(!$db_siteid || !$db_siteownerid || !$db_sitehash){
			$db_siteid      = generatestr(16);
			$db_siteownerid = generatestr(18);
			$db_sitehash = '10'.SitStrCode(md5($db_siteid.$db_siteownerid),md5($db_siteownerid.$db_siteid));
			$db->update("REPLACE INTO pw_config(db_name,db_value) VALUES ('db_siteid','$db_siteid')");
			$db->update("REPLACE INTO pw_config(db_name,db_value) VALUES ('db_siteownerid','$db_siteownerid')");
			$db->update("REPLACE INTO pw_config(db_name,db_value) VALUES ('db_sitehash','$db_sitehash')");
		}
	}
	$ifopen = 0;
	$ifopen_t && $ifopen += 1;
	$ifopen_s && $ifopen += 2;
	$rt=$db->get_one("SELECT db_name FROM pw_config WHERE db_name='db_siteifopen'");
	if($rt['db_name']){
		$db->update("UPDATE pw_config SET db_value='$ifopen' WHERE db_name='db_siteifopen'");
	}else{
		$db->update("INSERT INTO pw_config(db_name,db_value) VALUES ('db_siteifopen','$ifopen')");
	}
	updatecache_c();
	adminmsg('operate_success');
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