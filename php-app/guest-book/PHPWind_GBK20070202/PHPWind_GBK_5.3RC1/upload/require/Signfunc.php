<?php
!function_exists('readover') && exit('Forbidden');

function Signfunc($showsign,$starttime,$currency){
	global $db,$winduid,$groupid,$tdtime,$db_signgroup,$db_signmoney;

	if(!$starttime){
		$db->update("UPDATE pw_memberdata SET starttime='$tdtime',currency=currency-'$db_signmoney' WHERE uid='$winduid'");
	}elseif(!$db_signmoney || strpos($db_signgroup,",$groupid,") === false){
		$db->update("UPDATE pw_memberdata SET starttime='0' WHERE uid='$winduid'");
	}else{
		global $windid,$onlineip,$timestamp;
		$days = floor(($tdtime-$starttime)/86400);
		$cost = $days * $db_signmoney;
		if($currency >= $cost){
			$db->update("UPDATE pw_memberdata SET starttime='$tdtime',currency=currency-'$cost' WHERE uid='$winduid'");
		} else {
			$days = floor($currency/$db_signmoney);
			$cost = $days * $db_signmoney;
			$cost < 0 && $cost = 0;
			$db->update("UPDATE pw_memberdata SET starttime='0',currency=currency-'$cost' WHERE uid='$winduid'");
		}
		if($cost){
			require_once(R_P.'require/tool.php');
			$logdata=array(
				'type'		=>	'sign',
				'nums'		=>	0,
				'money'		=>	0,
				'descrip'	=>	'sign_descrip',
				'uid'		=>	$winduid,
				'username'	=>	$windid,
				'ip'		=>	$onlineip,
				'time'		=>	$timestamp,
				'currency'	=>	$cost
			);
			writetoollog($logdata);
		}
	}
}
?>