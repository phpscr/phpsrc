<?php
require_once('global.php');
include_once(D_P.'data/bbscache/ol_config.php');
if(!$ol_onlinepay){
	Showmsg($ol_whycolse);
}
if(!$ol_paypal || !$ol_paypalcode){
	Showmsg('olpay_seterror');
}
if($_GET['verifycode']!=$ol_paypalcode){
	Showmsg('undefined_action');
}elseif($payment_status=='Completed'){
	$rt = $db->get_one("SELECT c.*,m.username FROM pw_clientorder c LEFT JOIN pw_members m USING(uid) WHERE order_no='$invoice'");
	if($rt['state']=='0'){
		if($rt['number'] != $mc_gross){
			Showmsg('gross_error');
		}
		!$db_rmbrate && $db_rmbrate=10;
		$currency = $rt['number'] * $db_rmbrate;
		$number   = $rt['number'];
		$db->update("UPDATE pw_memberdata SET currency=currency+'$currency' WHERE uid='$rt[uid]'");
		$db->update("UPDATE pw_clientorder SET state=2,descrip='已完成订单' WHERE order_no='$invoice'");

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
		foreach($_POST as $key=>$value){
			$getdb .= $key."=".urlencode($value)."&";
		}
		$getdb .= 'date='.get_date($timestamp,'Y-m-d-H:i:s');
		$getdb .= '&site='.$_SERVER['HTTP_HOST'];
		@file("http://www.phpwind.com/pay/paypal.php?$getdb");exit;
	}else{
		Showmsg('undefined_action');
	}
}
?>