<?php
!function_exists('readover') && exit('Forbidden');
$thisday=get_date($GLOBALS['tdtime'],'Y-n-j');
$thismonth=get_date($GLOBALS['tdtime'],'Y-n');
$rt=$GLOBALS['db']->get_one("SELECT day FROM pw_ipstates WHERE day='$thisday'");
if($rt){
	$GLOBALS['db']->update("UPDATE pw_ipstates SET nums=nums+1 WHERE day='$thisday'");
}else{
	$GLOBALS['db']->update("INSERT INTO pw_ipstates(day,nums,month) VALUES('$thisday','1','$thismonth')");
}
Cookie('ipstate',$GLOBALS['timestamp']);
?>