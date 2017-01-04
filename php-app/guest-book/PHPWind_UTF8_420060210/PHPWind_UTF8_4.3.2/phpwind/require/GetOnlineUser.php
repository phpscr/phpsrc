<?php
!function_exists('readover') && exit('Forbidden');

function GetOnlineUser(){
	$onlinedb = openfile(D_P.'data/bbscache/online.php');
	if (count($onlinedb) == 1){
		$onlinedb = array();
	} else {
		unset($onlinedb[0]);
	}
	$onlineuser = array();
	foreach ($onlinedb as $key => $value){
		if (trim($value)){
			$dt = explode("\t",$value);
			$onlineuser[$dt[8]] = $dt[0];
		}
	}
	return $onlineuser;
}
?>