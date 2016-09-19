<?php
!function_exists('readover') && exit('Forbidden');
$actid=$read['activeid'];
$activity=$db->get_one("SELECT * FROM pw_activity WHERE id='$actid'");
$activity && $active=1;
foreach($activity as $key=>$val){
	if(in_array($key,array('starttime','endtime','deadline'))){
		$val=get_date($val,'Y-m-d');
	}
	${'active_'.$key}=$val;
}
unset($activity);
$query=$db->query("SELECT COUNT(*) AS count,state FROM pw_actmember WHERE actid='$actid' GROUP BY state");
$act_total=$act_y=0;
while($rt=$db->fetch_array($query)){
	$act_total+=$rt['count'];
	$rt['state']==1 && $act_y+=$rt['count'];
}
$actmen=$db->get_one("SELECT state FROM pw_actmember WHERE winduid='$winduid' AND actid='$actid'");
?>