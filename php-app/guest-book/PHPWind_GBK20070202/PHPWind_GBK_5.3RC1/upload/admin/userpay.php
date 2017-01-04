<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=userpay";

if (!$_POST['action']){
	include_once(D_P.'data/bbscache/ol_config.php');
	!$ol_paypalcode && $ol_paypalcode=RandString('40');
	ifcheck($ol_onlinepay,'onlinepay');
	include PrintEot('userpay');exit;
} else {
	!$userpay[ol_paypalcode] && $userpay[ol_paypalcode]=RandString('40');
	foreach($userpay as $key => $value){
		$db->pw_update(
			"SELECT db_name FROM pw_config WHERE db_name='$key'",
			"UPDATE pw_config SET db_value='$value' WHERE db_name='$key'",
			"INSERT INTO pw_config SET db_name='$key',db_value='$value'"
		);
	}
	updatecache_ol();
	adminmsg('operate_success');
}
function RandString($len){
	$rand='1234567890qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM';
	mt_srand((double)microtime() * 1000000);
	for($i=0;$i<$len;$i++){
		$code.=$rand[mt_rand(0,strlen($rand))];
	}
	return $code;
}
?>