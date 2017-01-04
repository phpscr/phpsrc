<?php
!function_exists('readover') && exit('Forbidden');

function GetCredit($uid){
	global $db;
	$credit = array();
	@include(D_P.'data/bbscache/creditdb.php');
	foreach($_CREDITDB as $key => $value){
		$credit[$key] = array($value[0],0);
	}
	$query = $db->query("SELECT cid,value FROM pw_membercredit WHERE uid='$uid'");
	while($rt = $db->fetch_array($query)){
		$credit[$rt['cid']]=array($_CREDITDB[$rt['cid']][0],$rt['value']);
	}
	return $credit;
}
?>