<?php
require_once('global.php');

include_once(D_P.'data/bbscache/ol_config.php');
if(!$ol_onlinepay){
	Showmsg($ol_whycolse);
}
if(!$ol_payto || !$ol_md5code){
	Showmsg('olpay_seterror');
}
$string = '';
foreach($_GET as $key => $value){
	if($key != 'ac'){
		$string   .= $key.$_GET[$key];
	}
}
$md5string = md5($string.$ol_md5code);
if ($ac != $md5string){
	pw_msg('N',1);
}
$rt = $db->get_one("SELECT c.*,m.username FROM pw_clientorder c LEFT JOIN pw_members m USING(uid) WHERE order_no='$order_no'");
if (!$rt){
	pw_msg('N',2);
}
if($action == "sendOff"){
	$db->update("UPDATE pw_clientorder SET state=1,descrip='已支付订单，等待用户确认' WHERE order_no='$order_no'");
	require_once(R_P.'require/msg.php');
	$message=array(
		$rt['username'],
		'',
		'olpay_title',
		$timestamp,
		'olpay_content',
		'',
		'SYSTEM'
	);
	writenewmsg($message,1);
	pw_msg('Y',4);
} elseif ($action == "checkOut"){
	if($rt['state'] == 2){
		pw_msg('Y',5);
	}
	!$db_rmbrate && $db_rmbrate=10;
	$currency = $rt['number'] * $db_rmbrate;
	$number   = $rt['number'];
	$db->update("UPDATE pw_memberdata SET currency=currency+'$currency' WHERE uid='$rt[uid]'");
	$db->update("UPDATE pw_clientorder SET payemail='$buyer_email',state=2,descrip='已完成订单' WHERE order_no='$order_no'");

	require_once(R_P.'require/tool.php');
	$logdata=array(
		'type'		=>	'olpay',
		'nums'		=>	0,
		'money'		=>	0,
		'descrip'	=>	'olpay_descrip',
		'uid'		=>	$rt['uid'],
		'username'	=>	$rt['username'],
		'ip'		=>	$onlineip,
		'time'		=>	$timestamp,
		'number'	=>	$number,
		'currency'	=>	$currency,
	);
	writetoollog($logdata);
	require_once(R_P.'require/msg.php');
	$message=array(
		$rt['username'],
		'',
		'olpay_title',
		$timestamp,
		"olpay_content_2",
		'',
		'SYSTEM'
	);
	writenewmsg($message,1);
	$getdb='';
	foreach($_GET as $key=>$value){
		$getdb .= $key."=".urlencode($value)."&";
	}
	$getdb .= 'date='.get_date($timestamp,'Y-m-d-H:i:s');
	$getdb .= '&site='.$_SERVER['HTTP_HOST'];
	@file("http://www.phpwind.com/pay/alipay.php?$getdb");
	pw_msg('Y',7);
} elseif ($action == "test"){
	pw_msg('Y',9);
} else {
	pw_msg('N',10);
}

function pw_msg($msg,$t=''){
	global $action,$msg_id;
	echo $msg;
	exit;
}
?>