<?php
!function_exists('readover') && exit('Forbidden');

if(!$passport_ifopen || $passport_type != 'server'){
	Showmsg('passport_close');
}

!$forward && $forward = $db_bbsurl;
$clienturl=explode("\n",str_replace("\r","",$passport_urls));

$jumpurl='';
while(!$jumpurl){
	$jumpurl=array_shift($clienturl);
}
if(!$jumpurl){
	Showmsg('undefined_action');
}

$userdb = array();
foreach($clienturl as $key=>$val){
	if($val && $val != $jumpurl){
		$userdb['url'] .= $userdb['url'] ? ",$val" : $val;
	}
}

$rt=$db->get_one("SELECT m.uid,m.username,m.password,m.email,md.rvrc,md.money,md.credit,currency FROM pw_members m LEFT JOIN pw_memberdata md USING(uid) WHERE m.uid='$winduid'");

$userdb['uid']		= $rt['uid'];
$userdb['username']	= $rt['username'];
$userdb['password']	= $rt['password'];
$userdb['email']	= $rt['email'];
$userdb['rvrc']		= $rt['rvrc'];
$userdb['money']	= $rt['money'];
$userdb['credit']	= $rt['credit'];
$userdb['currency']	= $rt['currency'];
$userdb['time']		= $timestamp;
$userdb['cktime']	= $cktime;

$userdb_encode='';
foreach($userdb as $key=>$val){
	$userdb_encode .= $userdb_encode ? "&$key=$val" : "$key=$val";
}
$db_hash=$passport_key;
$userdb_encode=str_replace('=','',StrCode($userdb_encode));

if($action=='login'){
	$verify = md5("login$userdb_encode$forward$passport_key");
	ObHeader("$jumpurl/passport_client.php?action=login&userdb=".rawurlencode($userdb_encode)."&forward=".rawurlencode($forward)."&verify=".rawurlencode($verify));
}elseif($action=='quit'){
	$verify = md5("quit$userdb_encode$forward$passport_key");
	ObHeader("$jumpurl/passport_client.php?action=quit&userdb=".rawurlencode($userdb_encode)."&forward=".rawurlencode($forward)."&verify=".rawurlencode($verify));
}
?>